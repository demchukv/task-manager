## Початок роботи з додатком

1. **Управління ролями**: Використано просто поле `role` (admin/member) в таблиці користувачів. Для продакшену краще було б використати пакет `spatie/laravel-permission` для управління ролями і правами доступу.
2. **Real-time**: Коментарі на сторінці завдання оновлюються з періодичністю 30 секунд. Для повноцінного real-time можна використати WebSockets (Laravel Echo/Reverb).
3. **Завантаження файлів**: Не реалізовано.
4. **API документація**: Використано `darkaonline/l5-swagger:^8.6` для створення API документації. Для доступу до документації запустіть `php artisan l5-swagger:generate` і відкрийте `http://localhost:8000/api/documentation`.

## Запуск

### .env
1. `cd backend && cp .env.example .env`
2. `cd frontend && cp .env.example .env`
3. `cd backend && php artisan key:generate`

### Backend
1. `cd backend`
2. `php artisan migrate:fresh --seed` (Використав SQLite по замовчуванню)
3. `php artisan serve`

### Frontend
1. `cd frontend`
2. `npm install`
3. `npm run dev`

### Tests
1. `cd backend`
2. `php artisan test`

## API документація
1. `cd backend`
2. `php artisan l5-swagger:generate`
3. `http://localhost:8000/api/documentation`

## Вхід і перевірка
1. Тестові дані для входу знаходяться в `backend\database\seeders\DatabaseSeeder.php`

## =================================
## Опис продукту: "Projects & Tasks"

Потрібно реалізувати спрощений менеджер проєктів і задач:

* Користувач може створювати проєкти.  
* У кожному проєкті — задачі з пріоритетом, статусом і виконавцем.  
* Коментарі до задач (простий тред без вкладень).  
* Фільтрація, пошук, пагінація на рівні API та UI.  
* Авторизація (логін/реєстрація), ролі: admin, member.  
* Розмежування доступів: бачити/редагувати можна лише свої проєкти, admin бачить усі.

Часовий бюджет: 6–8 годин. Якщо не встигаєте все — опишіть trade‑offs у README.

Технологічні вимоги

* Backend: Laravel 12, PHP 8.3+, Eloquent, Migrations, Seeders, Policies, Form Requests.  
* Frontend: Vue 3 (Composition API), Vite, Tailwind/Shad-cn  
* База: SQLite або MariaDB(на вибір). Для простоти — можна SQLite.  
* Тести: мінімум 5–8 Feature/Unit тестів на ключовий функціонал Policies/Controllers/Services).

Функціональні вимоги (Backend)

1. Аутентифікація  
   1. Email+password (можна Laravel Breeze/Jetstream або власна реалізація API токена: passport/sanctum).  
2. Моделі та зв’язки  
   1. User (id, name, email, role)  
   2. Project (id, name, description, owner\_id)  
   3. Task (id, project\_id, title, description, status: \[todo|in\_progress|done\], priority: \[low|medium|high\], assignee\_id, due\_date)  
   4. Comment (id, task\_id, user\_id, body)  
3. Доступи  
   1. Тільки власник проєкту або admin може редагувати/видаляти проєкт.  
4. Учасники з роллю member можуть створювати/редагувати задачі лише в проєктах, до яких їх додано.  
   1. Policies для Project і Task обов’язкові.  
5. REST API (прикладні ендпоїнти)  
   1. POST /api/login, POST /api/register, POST /api/logout  
   2. GET /api/projects (пагінація, пошук q по name, сортування sort=name|-name|created\_at|-created\_at)  
   3. POST /api/projects, GET /api/projects/{id}, PUT /api/projects/{id}, DELETE /api/projects/{id}  
   4. GET /api/projects/{id}/tasks (пагінація; фільтри: status, priority, assignee\_id; пошук q по title)  
   5. POST /api/projects/{id}/tasks, GET /api/tasks/{id}, PUT /api/tasks/{id}, DELETE /api/tasks/{id}  
   6. GET /api/tasks/{id}/comments, POST /api/tasks/{id}/comments  
6. Валідація  
   1. Використати FormRequest з чіткими повідомленнями про помилки.  
7. Архітектура  
   1. Дозволено Service шар для бізнес-логіки (наприклад, TaskService), але без надмірностей.  
   2. Відокремити DTO/Resource для відповіді: ProjectResource, TaskResource, CommentResource.

Функціональні вимоги (Frontend)

* Single Page App на Vue 3 з такими маршрутами:  
  * /login, /register  
  * /projects (список із пошуком/сортуванням/пагінацією)  
  * /projects/:id (деталі проєкту, список задач з фільтрами)  
  * /tasks/:id (деталі задачі \+ коментарі, форма додавання коментаря)  
* Стан аутентифікації зберігати в Pinia (token, user), перехоплювати 401 і редіректити на /login. (або на свій смак)  
* Форми створення/редагування проєктів і задач у модалках або окремих сторінках.  
* UI — простий, але акуратний (Tailwind або простий CSS). (може бути вбудований в стартеркіт(https://www.shadcn-vue.com/)  
* Дружній UX: показ спінерів, повідомлення про помилки валідації, порожні стани.
