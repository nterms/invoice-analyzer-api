# Invoice Analyzer API

## Setup

Clone the repository and move into the project folder
```sh
git clone git@github.com:nterms/invoice-analyzer-api.git
cd invoice-analyzer-api
```

This application uses Laravel Sail to run application on Docker.
Run following command to run the containers
```sh
./vendor/bin/sail up
```

Once the application's Docker containers have started, run database migrations:

```sh
./vendor/bin/sail artisan migrate
```

Open `.env` file and set the QWS account details

```
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_SESSION_TOKEN=
```

The API is available on `http://localhost` by default. If required to run on different port than port 80, set following values accordingly on `.env`
```
APP_URL=http://localhost:8090
APP_PORT=8090
```

You will need to set the same URL on front-end application as well.
