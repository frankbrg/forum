# Forum

## Installation

Use [docker and docker-compose](https://docs.docker.com/engine/install/ubuntu/) to install Forum.

To install the projet follow command between :
```bash
git clone git@github.com:frankbrg/forum.git
```
```bash
cd forum
```
```bash
docker-compose run --rm composer install
```

Now you need to create the database :

- Create a file .env.local with this information (You can change for connect other database)
    ```bash
    APP_ENV=dev
    DATABASE_URL="mysql://symfony:symfony@db:3306/db?charset=utf8mb4"
    ```
- Run you local environment
    ```bash
    docker-compose up -d
    ```
- Update the database
    ```bash
    docker-compose run --rm symfony console doctrine:migrations:migrate
    ```
    ```bash
    docker-compose run --rm symfony console doctrine:fixtures:load
    ```


- Now you can go to http://localhost:8000/, it's working !
## Usage

For connect as admin :

- username : admin
- password : admin123456

For connect as user :

- username : usertest
- password : usertest123

## Command
- For regenerate data
    ```bash
    docker-compose run --rm symfony console doctrine:fixtures:load
    ```
## License
[MIT](https://choosealicense.com/licenses/mit/)