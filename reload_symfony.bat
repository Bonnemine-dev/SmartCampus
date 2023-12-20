$CONTAINER_NAME = "but-info2-a-sae3-sfapp"

docker exec $CONTAINER_NAME bash -c "cd /app/sfapp && composer install && composer update && bin/console d:m:m && bin/console d:f:l"

Write-Host "Ton environnement symfony est prêt petit quoicouscrum <3"
