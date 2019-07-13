# Label API Docs

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
This will then return the created label like so:
```json
{
	"name": "example",
	"slug": "example",
	"path": "parent/sub-parent/example",
	"backgroundcolor": "#ffffff",
	"textcolor": "#cccccc"
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
This would return a full label mathcing the path, like so:
```json
{
	"name": "example",
	"slug": "example",
	"path": "parent/sub-parent/example",
	"backgroundcolor": "#ffffff",
	"textcolor": "#cccccc"
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
This will then return a full, updated label (with the **new** path if the slug or name was changed), like so:
```json
{
	"name": "updated-example",
	"slug": "updated-example",
	"path": "parent/sub-parent/updated-example",
	"backgroundcolor": "#ffffff",
	"textcolor": "#cccccc"
}
```