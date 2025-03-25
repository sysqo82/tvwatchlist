# TV Watchlist

This is a single page web application to allow a user to create a watchlist of TV shows.

## Deployment

In order to deploy this application, you will need first build the container before running it.

In both cases you will need to have an API key/pin for the TVDB API.

### Building and running the container

If you're planning to run the application on a remote server, you will need to build the container locally and then push it to a container registry. I'm using Dockerhub for this, but you can use any container registry you like. 

Alternatively, you can also build the container on the remote server. This will mean that you don't need to push the container to a registry but you will need to have Git installed on the remote server.

You will also need to access to a MongoDB instance.

#### Steps

1. Build the container using the production Dockerfile, which will install all the dependencies and build the application.

```bash
docker build -t tvwatchlist:latest -f docker/php/Dockerfile.prod .
```
_N.B. If you're building on one architecture and deploying on another, you will need to include the --platform for the architecture you're planning to deploy to avoid any unexpected errors._

2. Tag the container with the registry URL and push it to the registry.

_If you built on where you're planning to host, you can skip straight to step 4._

```bash
docker tag tvwatchlist:latest <registry_url>/tvwatchlist:latest
docker push <registry_url>/tvwatchlist:latest
```

3. SSH into the remote server and pull the container from the registry.

```bash
docker pull <registry_url>/tvwatchlist:latest
```

4. Run the container.

As part of the run command, you will need to provide the following environment variables:
- MONGODB_URL - This is the URL to your MongoDB instance
- TVDB_APIKEY - This is the API key for the TVDB API
- TVDB_PIN - This is the PIN for the TVDB API

You also need to a port to expose the application on.
```bash
docker run
  -d
  --name=<name>
  --net='bridge'
  -e 'MONGODB_URL'=<mongodb_url>
  -e 'TVDB_APIKEY'=<tvdb_apikey>
  -e 'TVDB_PIN'=<tvdb_pin>
  -p '<hostport>:80/tcp' tvwatchlist:latest
```

## Development

If you're planning to develop the application, you will need to build the container locally and then run it.

You will also need to have NPM, Composer and PHP installed locally.

### Steps

#### 1. Configure the environment variables.

There is a `.env.example` file in the root of the project. You will need to copy this to `.env` and update the APP_PORT value to whatever you want to access the application on.

There is also a `.env.example` file in the `app` directory where you need to add your TVDB API key and PIN.

#### 2. Install NPM dependencies.

```bash
npm install
```

#### 3. Spin up the application using `docker compose`.

This will spin up the application and make it accessible in the browser. As part of this is will install the php dependencies and build the application.

```bash
docker compose up -d && docker compose logs -f
```

Once you see the following message in the logs, you can access the application at http://localhost:10000. 

```
tvwatchlist-app-1  | [04-Jan-2024 19:48:26] NOTICE: fpm is running, pid 1
tvwatchlist-app-1  | [04-Jan-2024 19:48:26] NOTICE: ready to handle connections
```

`10000` is the default `APP_PORT` value in the `.env.example` file. The port number in the url needs to match the `APP_PORT` value in the `.env` file.

#### 4. Build js and css files.

Whilst the application is up and running, it is not yet ready to be used. You will need to build the js and css files.

```bash
npm run dev
```

This will build the files and then you will be able to use the application. 

If you want to see any changes you make to the js and css files, you can re-run the build command or run the watch command.

```bash
npm run watch
```

This will watch for any changes to the js and css files and rebuild them when it detects a change.
