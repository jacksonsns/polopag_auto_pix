# canary_auto_pix
```markdown
# Works with PIX from: https://polopag.com

## Instructions for Linux Machines
```
### For Newer Linux Machines

1. **Update package lists for upgrades and new package installations:**
  ```sh
  sudo apt update
  ```

2. **Install MySQL connector for Python:**
  ```sh
  python3 -m pip install mysql-connector-python
  ```
  
### For Older Linux Machines
1. **Update package lists for upgrades and new package installations:**
  ```sh
  sudo apt update
  ```

2. **Install Python 3.7:**
  ```sh
  sudo apt install python3.7
  ```

3. **Verify Python 3.7 installation:**
  ```sh
  python3.7 -V
  ```

4. **Verify pip for Python 3.7 installation (Install pip for Python 3.7 if not already installed):**
  ```sh
  pip3.7 -V
  python3.7 -m pip install --upgrade pip
  ```

6. **Install MySQL connector for Python 3.7:**
  ```sh
  python3.7 -m pip install mysql-connector-python
  ```

7. **Note:** If your machine is old, remember to change `python3` to `python3.7` in the `pix_class` script.

## SQL Statement to Create Table

Use the following SQL statement to create the `polopag_transacoes` table:
```sql
CREATE TABLE `polopag_transacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `reference` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `txid` varchar(255) NOT NULL,
  `internalId` varchar(255) NOT NULL,
  `base64` mediumtext DEFAULT NULL,
  `copia_e_cola` mediumtext DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `points` int(11) DEFAULT NULL,
  `coins_table` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `origin` enum('game', 'site') NOT NULL DEFAULT 'site',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

## File Configuration

1. **Edit `pix_class.lua`:** Customize the file with your preferences and `api_key`.

2. **Place `polopag.py`:** Ensure `polopag.py` is in the main directory alongside `config.lua`.

3. **Place `polopag_webhook.php`:** Ensure `polopag_webhook.php` is in the main directory of your site alongside `index.php`.

4. **Configure `polopag_webhook.php`:** Open the file and set the path to `config.lua`.
