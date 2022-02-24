#!/usr/bin/env bash

echo "started upgrade command\n"
sudo php /opt/bitnami/magento/bin/magento setup:upgrade
echo "finished upgrade command\n"
echo "started compile command\n"
sudo php /opt/bitnami/magento/bin/magento setup:di:compile
echo "finished compile command\n"
#echo "started deploy command\n"
#sudo php /opt/bitnami/magento/bin/magento setup:static-content:deploy
#echo "finished deploy command\n"
