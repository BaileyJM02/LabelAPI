<h1 align="center">Label API</h1>
<p align="center"><a href="#docs">Docs</a><br/>
<a href="#setup">Setup</a><br/>
<a href="#walk-through">Walk-through</a><br/>
<a href="#testing">Testing</a>
</p>

Honestly, considering I haven't used any of the given frameworks before, I think I did an alright job. If you could show me how to get the tests working, that would be great!

# Docs

## **Path**: /api/label
**Methods**:  POST

This needs all data for a label to be given, otherwise one can not be created, like so:
```json
{
	"name": "example",
	"slug": "example",
	"path": "example",
	"backgroundcolor": "#ffffff",
	"textcolor": "#cccccc"
}
```
a nested example would look like so, where the parent would be matched with a label which has a path of `parent`, and the sub-parent would have a path of `parent/sub-parent` :
```json
{
	"name": "example",
	"slug": "example",
	"path": "parent/sub-parent/example",
	"backgroundcolor": "#ffffff",
	"textcolor": "#cccccc"
}
```
This will then return the created label like so, encased in a success response:
```json
{
    "status": "success",
    "data": {
		"name": "example",
		"slug": "example",
		"path": "parent/sub-parent/example",
		"backgroundcolor": "#ffffff",
		"textcolor": "#cccccc"
    },
    "code": 200
}
```

## **Path**: /api/label
**Methods**:  GET

This only needs the path to fetch the correct label, all other keys will be ignored:
```json
{
	"path": "example"
}
```
a nested example would look like so, where the label would have to have a parent of `parent` and `parent/sub-parent` :
```json
{
	"path": "parent/sub-parent/example"
}
```
This would return a full label matching the path, like so, encased in a success response:
```json
{
    "status": "success",
    "data": {
		"name": "example",
		"slug": "example",
		"path": "parent/sub-parent/example",
		"backgroundcolor": "#ffffff",
		"textcolor": "#cccccc"
    },
    "code": 200
}
```

## **Path**: /api/label
**Methods**:  PUT, PATCH

This needs the path, to fetch the correct label, **and** the value to be updated:
```json
{
	"path": "example",
	"name": "updated-example"
}
```
a nested example would look like so, where the label would have to have a parent of `parent` and `parent/sub-parent` :
```json
{
	"path": "parent/sub-parent/example",
	"name": "updated-example"
}
```
This will then return a full, updated label (with the **new** path if the slug or name was changed), like so, encased in a success response:
```json
{
	    "status": "success",
    "data": {
		"name": "updated-example",
		"slug": "updated-example",
		"path": "parent/sub-parent/updated-example",
		"backgroundcolor": "#ffffff",
		"textcolor": "#cccccc"
    },
    "code": 200
}
```

## **Path**: /api/label
**Methods**:  DELETE

This only needs the path to delete the correct label, all other keys will be ignored:
```json
{
	"path": "example"
}
```
a nested example would look like so, where the label would have to have a parent of `parent` and `parent/sub-parent` :
```json
{
	"path": "parent/sub-parent/example"
}
```
This would return a full label matching the path, like so, encased in a success response. Though it will not longer exist in the database:
```json
{
    "status": "success",
    "data": {
		"name": "example",
		"slug": "example",
		"path": "parent/sub-parent/example",
		"backgroundcolor": "#ffffff",
		"textcolor": "#cccccc"
    },
    "code": 200
}
```


# Setup

This should be fairly simple to setup. All the following commands require you to be in the directory of labelAPI. eg. `/labelAPI/`.

1. Install the dependencies using composer: `php composer.phar install` or `composer install`.
2. Setup the database:
	1. Enter the `.env` file and replace line 29 with your credentials for the MySQL database. Currently `DATABASE_URL=mysql://user:UserPassw0rd@localhost:3306/labelapi`.
	2. Run `php bin/console doctrine:database:create` to create the database.
	3. As we already have an entity file created, we need to run `php bin/console make:migration` to create a file containing SQL needed to update the database.
	4. To execute the file made above, we need to run this next command: `php bin/console doctrine:migrations:migrate`. Now, the database should be fully setup.
3. We now need to setup TLS using this command: `symfony server:ca:install`, this will create a local certificate authority that allows us to locally connect over HTTPS.
4. Finally, we run `symfony server:start` to server the app.

# Walk-through

> I wasn't sure what you meant by "We will like a Command action to see on terminal the Json responses", so, I opted for a set of cURL commands that need to be pasted into the terminal, trying to keep it simple.

These commands follow the order described within the project file. The `--insecure` flag was needed as using cURL over HTTP just resulted in a redirect message, therefore we needed to use HTTPS *but* the certificate was self signed.

We also have to escape each double quote value as single quotes are no longer supported on windows.

> All commands use the URL `https://127.0.0.1:8000/api/label`. The port may be different if port `8000` is already in use and Symfony decides to use another one.
## Create Label "Heating engineer"
- color: black
- background-color: orange

`curl -X POST --insecure -d "{\"name\":\"Heating engineer\", \"slug\": \"heating engineer\",\"path\": \"heating-engineer\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#FFA500\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Heating engineer",
	"slug": "heating-engineer",
	"path": "heating-engineer",
	"textcolor": "#000000",
	"backgroundcolor": "#FFA500"
}
```

## Create Label "Electricians"
- color: black
- background-color: green

`curl -X POST --insecure -d "{\"name\":\"Electricians\", \"slug\": \"electricians\",\"path\": \"electricians\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#008000\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Electricians",
	"slug": "electricians",
	"path": "electricians",
	"textcolor": "#000000",
	"backgroundcolor": "#008000"
}
```

## Create Label "Boiler"
- Nested under: "Heating engineer"

`curl -X POST --insecure -d "{\"name\":\"Boiler\", \"slug\": \"boiler\",\"path\": \"heating-engineer/boiler\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Boiler",
	"slug": "boiler",
	"path": "heating-engineer/boiler",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```

## Create Label "Baxi"
- Nested under: "Boiler"

`curl -X POST --insecure -d "{\"name\":\"Baxi\", \"slug\": \"baxi\",\"path\": \"heating-engineer/boiler/baxi\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Baxi",
	"slug": "baxi",
	"path": "heating-engineer/boiler/baxi",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```

## Create Label "Ideal"
- Nested under: "Boiler"

`curl -X POST --insecure -d "{\"name\":\"Ideal\", \"slug\": \"ideal\",\"path\": \"heating-engineer/boiler/ideal\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Ideal",
	"slug": "ideal",
	"path": "heating-engineer/boiler/ideal",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```

## Create Label "Cylinder"
- Nested under: "Heating engineer"

`curl -X POST --insecure -d "{\"name\":\"Cylinder\", \"slug\": \"cylinder\",\"path\": \"heating-engineer/cylinder\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Cylinder",
	"slug": "cylinder",
	"path": "heating-engineer/cylinder",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```

## Get Label "Heating engineer"

`curl -X GET --insecure -d "{\"path\": \"heating-engineer\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"path": "heating-engineer"
}
```

## Update Label "Ideal" to "Worcester"

`curl -X PUT --insecure -d "{\"name\":\"Worcester\", \"path\": \"heating-engineer/boiler/ideal\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Worcester",
	"path": "heating-engineer/boiler/ideal"
}
```

## Delete Label "Baxi"

`curl -X DELETE --insecure -d "{\"path\": \"heating-engineer/boiler/baxi\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"path": "heating-engineer/boiler/baxi"
}
```

## Get Label "Heating engineer"

`curl -X GET --insecure -d "{\"path\": \"heating-engineer\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"path": "heating-engineer"
}
```

## Create Label "Baxi"
- Nested under: "Boiler"

`curl -X POST --insecure -d "{\"name\":\"Baxi\", \"slug\": \"baxi\",\"path\": \"heating-engineer/boiler/baxi\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Baxi",
	"slug": "baxi",
	"path": "heating-engineer/boiler/baxi",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```

## Create Label "Worcester"
- Nested under: "Boiler"

`curl -X POST --insecure -d "{\"name\":\"Worcester\", \"slug\": \"worcester\",\"path\": \"heating-engineer/boiler/worcester\",\"textcolor\": \"#000000\",\"backgroundcolor\": \"#ffffff\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"name": "Worcester",
	"slug": "worcester",
	"path": "heating-engineer/boiler/worcester",
	"textcolor": "#000000",
	"backgroundcolor": "#ffffff"
}
```
> As expected, returns an error. (Code: 1016)

## Get Label "Heating engineer"

`curl -X GET --insecure -d "{\"path\": \"heating-engineer\"}" -H "Content-Type: application/json" https://127.0.0.1:8000/api/label`

JSON Sent:
```json
{
	"path": "heating-engineer"
}
```

This should give you a final output of:
```json
{
  "status":"success",
  "data":{
    "name":"Heating engineer",
    "slug":"heating-engineer",
    "path":"heating-engineer",
    "textcolor":"#000000",
    "backgroundcolor":"#FFA500",
    "nested":[
      {
        "name":"Boiler",
        "slug":"boiler",
        "path":"heating-engineer/boiler",
        "textcolor":"#000000",
        "backgroundcolor":"#ffffff"
      },
      {
        "name":"Worcester",
        "slug":"Worcester",
        "path":"heating-engineer/boiler/worcester",
        "textcolor":"#000000",
        "backgroundcolor":"#ffffff"
      },
      {
        "name":"Cylinder",
        "slug":"cylinder",
        "path":"heating-engineer/cylinder",
        "textcolor":"#000000",
        "backgroundcolor":"#ffffff"
      },
      {
        "name":"Baxi",
        "slug":"baxi",
        "path":"heating-engineer/boiler/baxi",
        "textcolor":"#000000",
        "backgroundcolor":"#ffffff"
      }
    ]
  },
  "code":200
}
```

# Testing

To setup testing, please follow these steps:
1. Enter the `.env.test` file and replace line 6 with your credentials for the **test** MySQL database. Currently `DATABASE_URL=mysql://user:UserPassw0rd@localhost:3306/labelapitest`.
2. Run `php bin/console doctrine:database:create -e test` to create the database.
3. As we already have an entity file created, we **do not need** to run `php bin/console make:migration` again to create a file containing SQL needed to update the new database, it will use the old one.
4. We need to run this next command to use the previous migration file: `php bin/console doctrine:migrations:migrate -e test`. Now, the test database should be fully setup.

To run the tests, use the command `php bin/phpunit`.

Output should be along the lines of:
```console
OK (48 tests, 144 assertions)
```