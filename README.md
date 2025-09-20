# News Aggregator Backend

A Laravel-based backend service that aggregates news articles from multiple sources (NewsAPI, The Guardian, and New York Times) and provides API endpoints for retrieving, filtering, and searching articles.

## Project Requirements

### Data Sources That Can Be Used (Choose At Least 3)
- NewsAPI: Access to articles from more than 70,000 news sources, including major newspapers, magazines, and blogs
- OpenNews: Access to news content from various sources with retrieval based on keywords, categories, and sources
- NewsCred: Access to news content with retrieval options for keywords, categories, sources, authors, publications, and topics
- The Guardian: Access to articles from The Guardian newspaper with category filtering and search capabilities
- New York Times: Access to articles from The New York Times with category filtering and search capabilities
- BBC News: Access to news from BBC News with category filtering and search capabilities
- NewsAPI.org: Access to news articles from thousands of sources with retrieval based on keywords, categories, and sources

### Requirements
1. **Data aggregation and storage**: Implement a backend system that fetches articles from selected data sources (at least 3) and stores them locally in a database. Ensure regular updates from live data sources.
2. **API endpoints**: Create API endpoints for frontend interaction to retrieve articles based on search queries, filtering criteria (date, category, source), and user preferences (selected sources, categories, authors).

### Expected Output
- Backend project using PHP Laravel
- Data fetching and storage mechanisms for selected data sources with regular updates
- API endpoints for frontend interaction with search, filtering, and preference-based retrieval
- Implementation of software development best practices: DRY (Don't Repeat Yourself), KISS (Keep It Simple, Stupid), and SOLID principles

## Features

- Aggregates news articles from multiple sources:
  - NewsAPI (https://newsapi.org)
  - The Guardian (https://open-platform.theguardian.com)
  - New York Times (https://developer.nytimes.com)
- Stores articles and sources in a database for efficient retrieval
- Provides API endpoints for:
  - Retrieving all articles with filtering options
  - Searching articles by keywords
  - Getting articles from specific sources
  - Getting article categories and authors
- User preferences for personalized news feeds

## Requirements

- PHP 8.1 or higher
- Composer
- Laravel 12.x
- SQLite (or other database of your choice)
- API keys for the news sources

## Installation

This script will:
1. Install dependencies
2. Create the .env file
3. Generate an application key
4. Create the SQLite database
5. Run migrations
6. Prompt you for API keys and update the .env file


1. Clone the repository:
```
git clone 
cd innoscripta
```

2. Install dependencies:
```
composer install
```

3. Copy the `.env.example` file to `.env` and configure your environment:
```
cp .env.example .env
```

4. Generate an application key:
```
php artisan key:generate
```

5. Configure your database in the `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=innoscripta
DB_USERNAME=your_mysql_username
DB_PASSWORD=your_mysql_password
```

6. Create the MySQL database:
```
mysql -u your_mysql_username -p -e "CREATE DATABASE innoscripta"
```

7. Run migrations:
```
php artisan migrate
```

8. Add your API keys to the `.env` file:
```
NEWSAPI_KEY=your_newsapi_key
GUARDIAN_API_KEY=your_guardian_api_key
NYT_API_KEY=your_nyt_api_key
NYT_API_SECRET=your_nyt_api_secret
```

9. Seed the database with default users:
```
php artisan db:seed --class=UserSeeder
```

This will create the following test users:
- Regular User:
  - Email: john@example.com
  - Password: password
- Admin User:
  - Email: admin@example.com
  - Password: password

## Usage

### Running the Application

```
php artisan serve
```

### Fetching Articles Manually

```
php artisan news:fetch-articles
```

### Fetching Sources Manually

```
php artisan news:fetch-sources
```


### Postman Collection

A Postman collection is included in the repository to help you test the API endpoints. To use it:

1. Import the collection file into Postman:
   ```
   postman/NewsAggregator.postman_collection.json
   ```

2. Import the environment file:
   ```
   postman/NewsAggregator.postman_environment.json
   ```

3. Set the environment variables:
   - `base_url`: The base URL of your API (default: http://localhost:8000)
   - `auth_token`: Your authentication token (after login)

### API Endpoints

#### Articles

- `GET /api/articles` - Get all articles with optional filtering
  - Query parameters:
    - `search` - Search term
    - `source_id` - Filter by source ID
    - `category` - Filter by category
    - `author` - Filter by author
    - `date_from` - Filter by date (from)
    - `date_to` - Filter by date (to)
    - `per_page` - Number of articles per page
    - `page` - Page number

- `GET /api/articles/search` - Search articles
  - Query parameters:
    - `query` - Search term (required)
    - Other filters as above

- `GET /api/articles/categories` - Get all article categories

- `GET /api/articles/authors` - Get all article authors

#### Sources

- `GET /api/sources` - Get all sources

- `GET /api/sources/{id}/articles` - Get articles from a specific source
  - Query parameters: same as `/api/articles`

#### User Preferences (requires authentication)

- `GET /api/preferences` - Get user preferences

- `PUT /api/preferences` - Update user preferences
  - Body parameters:
    - `preferred_sources` - Array of source IDs
    - `preferred_categories` - Array of categories
    - `preferred_authors` - Array of authors

- `GET /api/preferences/articles` - Get articles based on user preferences
  - Query parameters: same as `/api/articles`

#### Authentication

- `POST /api/login` - Login to get an authentication token
  - Body parameters:
    - `email` - User email
    - `password` - User password

- `POST /api/register` - Register a new user
  - Body parameters:
    - `name` - User name
    - `email` - User email
    - `password` - User password
    - `password_confirmation` - Password confirmation

- `POST /api/logout` - Logout and invalidate the current token (requires authentication)

- `GET /api/user` - Get the authenticated user's details (requires authentication)

## Testing

Run the tests with:

```
php artisan test
```

## Architecture

The application follows the SOLID principles and uses a service-oriented architecture:

- **Models**: Define the database structure and relationships
- **Controllers**: Handle HTTP requests and responses
- **Services**: Contain business logic for fetching and processing articles
  - **NewsServices**: Contains implementations for different news sources (NewsAPI, Guardian, New York Times)
  - **Contracts**: Define interfaces and base classes for news services
- **Interfaces**: Define contracts for services to follow
- **Factories**: Generate test data

## License

This project is licensed under the MIT License.