# Сервис комментариев

------
**TLDR:**
Это тестовое задание для стажировки PHP-разработчика в ВК. Нужно создать сервис комментариев с использованием четырех API методов: POST, PUT, GET и DELETE. Необходимо предоставить возможность ответить на комментарий, а также получить полное дерево комментариев.

## 🛠 Tech stack

------
**TL;DR: Symfony, MySQL, Nginx, Api Platform, Docker Compose**

Сервиc реализован на фреймворке **Symfony**, в паре с ним используется СУБД **Mysgl** и сервер **Nginx**, также есть поддержка **Open API** с помощью API Platform.

## 🔮 Установка и запуск

------
1. Install [Docker](https://www.docker.com/get-started/)
2. Clone the repo
```bash
$ git clone https://github.com/m-fedosov/vk-contest.git
$ cd vk-contest
```
3. Run
```bash
$ docker-compose up
```
Таким образом запуститься сервис комментариев с подключённой базой данных и Nginx сервер. Переходим по адресу http://localhost/api и получаем доступ к Open API. 

Если всё сломалось, пересобираем проект:

```bash
$ docker-compose up --build -d
```

Возможно не созданы таблицы Comment и User, создаём:
```bash
$ docker compose exec php-cli ./bin/console doctrine:schema:create
```

И можно запуситить миграции Symfony:
```bash
$ docker compose exec php-cli ./bin/console make:migration -n
$ docker compose exec php-cli ./bin/console doctrine:migrations:migrate -n
```

## ☄️ Работа с сервисом

------
Поскольку входные и выходные данные должны быть в формате JSON. То удобнее всего взаимодейстовать с сервисом через **Postman** или **Insomnia**. 

Для всех запросов нужно указывать Content-Type: application/json (Для Insomnia перейдите в headers запрос и укажите это поле)

#### Примеры запросов:
1. Создадим пользователя

   **Post** запрос по адресу http://localhost/api/register

```json
{
  "email": "yeaaahbudddy@vk.com",
  "password": "wow_so_secret"
}
```

2. Получим JWT токен для авторизации пользователя:
   
   **Post** запрос по адресу http://localhost/api/login_check

```json
{
  "email": "yeaaahbudddy@vk.com",
  "password": "wow_so_secret"
}
```

В ответе получим токен в формате Json:
```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2ODM5MDIwMjEsImV4cCI6MTY4MzkwNTYyMSwicm9sZXMiOlsiUk9MRV9VU0VSIl0sInVzZXJuYW1lIjoiZmVkb3NtaWtlMUBnbWFpbC5jb20ifQ.MZK0YgMt7E3Yn5c89M2lC394GSkLFf90DmDSOCzVCrpu4ZVycukFyOK1EzrKEd90dGsQgW3Sj8u0cWYILuLvsK-0V8zJA45BdDrq3tnefRsUq5zcAO1-c2S5FVs8fNQDv0qlzhL9c2z9GfRK7l4Q72vCkGPonOpwD5df9oT3TszJh13EaVDJ-4h8uVOJoploj98bYIYDapknJsuhgIxL0XsXry8f93am0YBrnOjBwdztKMHkETkpom77hVfAeq6QXiTvNuWYb3g50Qf45hsaCZcuxkR61vZb3DiWT5qy_H0ve2mrmelXGv0FCG008xou69HydegM9umJ2qzmUXoH1Q"
}
```

Этот токен стоит добавить в env переменную своего клиента. Для Insomnia: Ctrl+E и так и вставляем (that's what she said)

4. При работе с комментариями можно обращаться только через авторизованного пользователя. Для всех запросов `\comment` нужно указывать Authorization: Bearer {{token}} (Для Insomnia перейдите в headers запрос и укажите это поле). В итоге будут использованы 2 поля: Authorization и Content-Type

5. Создание комментария

   **Post** запрос по адресу http://localhost/comment

```json
{
	"text": "8 800 555 35 35"
}
```

Можно указать id существующего комментария, тогда этот комментарий будет ответом

```json
{
  "text": "call me baby",
  "parent_id": 2
}
```
6. Получение комментария:

   **Get** запрос по адресу http://localhost/comment/{id}
    
   В ответе получим комментарий и полное дерево комментариев в виде Json
```json
{
  "id": 4,
  "text": "your second comment is dumb",
  "created_at": "2023-05-12 07:07:54",
  "user_id": 6,
  "parent_comment": {
    "id": 2,
    "text": "hey, my second comment",
    "created_at": "2023-05-12 07:07:30",
    "user_id": 6,
    "parent_comment": {
      "id": 1,
      "text": "edit comment",
      "created_at": "2023-05-12 07:07:21",
      "user_id": 6,
      "parent_comment": null
    }
  }
}
```

7. Обновление комментария: 

   **Put** запрос по адресу http://localhost/comment/{id}

    Мы помним про то, что авторизация происходит по токенам заголовка, так что остаётся просто передать новый текст комментария. Естетвенно редактировать можно только свои комментарии:

```json
{
  "text": "edit comment"
}
```

8. Удаление комментария: 

   **Delete** запрос по адресу http://localhost/comment/{id}

    Удалять тоже можно только свои комментарии, никакой Json передавать не нужно :)


**Всё**

## 👩‍💼 License

------
MIT

Другими словами, вы можете использовать код в частных и коммерческих целях с указанием авторства.

Не стесняйтесь написать мне на почту: [mifedosov@miem.hse.ru](mailto:mifedosov@miem.hse.ru)

❤️
