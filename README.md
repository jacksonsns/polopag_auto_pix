# canary_auto_pix
```markdown

# Works with PIX from: https://polopag.com

# Update package lists for upgrades and new package installations
sudo apt update

# Install Python 3.7
sudo apt install python3.7

# Verify Python 3.7 installation
python3.7 -V

# Verify pip for Python 3.7 installation (Install pip for python 3.7 if not exists)
pip3.7 -V

# Install MySQL connector for Python
python3.7 -m pip install mysql-connector-python

# SQL statement to create.
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

# Edit the file pix_class.lua with your preferences and api_key.

# polopag.py has to be in the main directory alongside config.lua

# polopag_webhook.php must be in the main directory of your site alongside index.php

# polopag_webhook.php requires configuration (Open the file and set the path to config.lua)
