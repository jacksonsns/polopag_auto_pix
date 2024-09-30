import requests
import sys
import mysql.connector
import re
import os
from mysql.connector import Error
from datetime import datetime, timedelta

script_dir = os.path.dirname(os.path.abspath(__file__))
lua_config_path = os.path.join(script_dir, 'config.lua')

def read_config():
    config = {}
    try:
        with open(lua_config_path, 'r') as file:
            lines = file.readlines()
            for line in lines:
                if 'mysqlHost' in line:
                    config['host'] = re.search(r'"(.*?)"', line).group(1)
                elif 'mysqlUser' in line:
                    config['user'] = re.search(r'"(.*?)"', line).group(1)
                elif 'mysqlPass' in line:
                    config['password'] = re.search(r'"(.*?)"', line).group(1)
                elif 'mysqlDatabase' in line:
                    config['database'] = re.search(r'"(.*?)"', line).group(1)
                elif 'mysqlPort' in line:
                    config['port'] = int(re.search(r'(\d+)', line).group(1))
    except FileNotFoundError as e:
        print(f"Erro: arquivo config.lua nao encontrado. {e}")
    except Exception as e:
        print(f"Erro ao processar o arquivo de configuracaoo: {e}")
    
    return config

def connect_to_database():
    config = read_config()
    if not config:
        print("Erro: ao carregar config.")
        return None

    try:
        connection = mysql.connector.connect(
            host=config.get('host', 'localhost'),
            user=config.get('user', 'root'),
            password=config.get('password', ''),
            database=config.get('database', ''),
            port=config.get('port', 3306)
        )
        return connection
    except Error as e:
        print(f"Erro ao conectar ao banco de dados: {e}")
        return None

def generate_pix(valor, api_key, reference, solicitacao_pagador, webhook_url):
    url = "https://api.polopag.com/v1/cobpix"
    headers = {
        "Api-Key": api_key,
        "Content-Type": "application/json"
    }

    data = {
        "valor": valor,
        "calendario": {
            "expiracao": 3600  #1 hora
        },
        "isDeposit": False,
        "referencia": reference,
        "solicitacaoPagador": solicitacao_pagador,
        "infoAdicionais": [],
        "webhookUrl": webhook_url
    }

    try:
        response = requests.post(url, headers=headers, json=data)
        if response.status_code == 200:
            return response.json()
        else:
            print(f"Erro na API: {response.status_code}")
            print(f"Resposta completa da API: {response.text}")
            return None
    except requests.exceptions.RequestException as e:
        print(f"Erro de requisicao: {e}")
        return None

def save_pix_data_to_db(txid, base64_qrcode, copia_e_cola, valor, points, coins_table, expires_at, status, reference, account_id, internal_id):
    connection = connect_to_database()
    if connection:
        cursor = connection.cursor()
        try:
            query = """INSERT INTO polopag_transacoes (account_id, txid, base64, copia_e_cola, price, points, coins_table, expires_at, status, reference, type, internalId) 
                       VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, "PIX", %s, "game")"""
            cursor.execute(query, (account_id, txid, base64_qrcode, copia_e_cola, valor, points, coins_table, expires_at, status, reference, internal_id))
            connection.commit()
            print("QRCode PIX e internalId salvo com sucesso.")
        except Error as e:
            print(f"Erro ao salvar os dados: {e}")
        finally:
            cursor.close()
            connection.close()

def process_pix(api_key, valor, solicitacao_pagador, webhook_url, points, reference, coins_table, account_id):
    pix_data = generate_pix(valor, api_key, reference, solicitacao_pagador, webhook_url)
    if pix_data:
        txid = pix_data.get("txid")
        base64_qrcode = pix_data.get("qrcodeBase64")
        copia_e_cola = pix_data.get("pixCopiaECola")
        internal_id = pix_data.get("internalId")
        status = "ATIVA"

        expires_at = (datetime.now() + timedelta(seconds=3600)).strftime('%Y-%m-%d %H:%M:%S') #1 hora

        save_pix_data_to_db(txid, base64_qrcode, copia_e_cola, valor, points, coins_table, expires_at, status, reference, account_id, internal_id)

if __name__ == "__main__":
    api_key = sys.argv[1]
    valor = sys.argv[2]
    solicitacao_pagador = sys.argv[3]
    webhook_url = sys.argv[4]
    points = sys.argv[5]
    reference = sys.argv[6]
    coins_table = sys.argv[7]
    account_id = sys.argv[8]

    process_pix(api_key, valor, solicitacao_pagador, webhook_url, points, reference, coins_table, account_id)
