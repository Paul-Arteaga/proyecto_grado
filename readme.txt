Pasos de instalacion


1.- composer install
2.- copiar .env
3.- php artisan key:generate
4.- php artisan migrate:fresh --seed
5.- php artisan storage:link



//////////////////////////////////////
///////COMANDOS DOCKER/////////////////////
docker-compose up -d --build
docker-compose up -d //levanta el servicio
docker-compose down //apagar el servicio


docker-compose logs "nombre contenedor"

docker-compose exec app bash
php artisan migrate:fresh --seed


/////git comandos///////

git init
git add .
git commit -m "primer commit"
git remote add origin https://github.com/erickpch/si1-2-2025.git
git push origin master/main