What is it
==========

Docker container for building personal feeds or chats.

Installation
============

```bash
docker run \
-p 80:80/tcp \
-e PG_HOST=db \
-e PG_PORT=5432 \
-e PG_DATABASE=feed_db \
-e PG_USER=user \
-e PG_PASSWORD=password \
-d perfumerlabs/feed:v1.0.0
```

Database must be created before container startup.

Environment variables
=====================

- CENTRIFUGO_HOST - host of centrifugo server. Optional.
- CENTRIFUGO_API_KEY - Centrifugo API Key. Optional.
- CENTRIFUGO_SECRET_KEY - Centrifugo Secret Key. Optional.
- BADGES_HOST - host of Badges server. Optional.
- PG_HOST - PostgreSQL host. Required.
- PG_PORT - PostgreSQL port. Default value is 5432.
- PG_DATABASE - PostgreSQL database name. Required.
- PG_USER - PostgreSQL user name. Required.
- PG_PASSWORD - PostgreSQL user password. Required.
- PHP_PM_MAX_CHILDREN - number of FPM workers. Default value is 10.
- PHP_PM_MAX_REQUESTS - number of FPM max requests. Default value is 500.

Volumes
=======

This image has no volumes.

If you want to make any additional configuration of container, mount your bash script to /opt/setup.sh. This script will be executed on container setup.

How it works
============

This microservice is invented to provide data structure and API for personal feeds like notification feed,
some kind of article feed with personal algorithms, chats and so on.

When a record appears you push it to Feed. Then you can fetch a number of records
from the top of list or fetch a particular record and send it to client-side.
Feed has integration with websocket service [Centrifugo](https://github.com/centrifugal/centrifugo) and [Badges](https://github.com/perfumerlabs/badges) microservice.
If config for those integrations is set, then Feed will send pushes to the service by itself.
Also Feed will automatically delete badges from [Badges](https://github.com/perfumerlabs/badges) when read API is called.

Database tables
===============

After setup there are 1 predefined table in database:

### feed_collection

Registry of collections. Fields:

- name [string] - Name of collection
- websocket_module [string] - module of websocket pushes.
- badges_collection [string] - collection of Badges to push badges to.
- badges_prefix [string] - prefix of badges names.

API Reference
=============

### Create a collection

`POST /collection`

Parameters (json):
- name [string,required] - name of the collection.
- websocket_module [string,optional] - module of websocket pushes.
- badges_collection [string,optional] - collection of Badges to push badges to.
- badges_prefix [string,optional] - prefix of badges names.

Request example:

```json
{
    "name": "foobar"
}
```

Response example:

```json
{
    "status": true
}
```

### Update collection

`PATCH /collection/{name}`

Parameters (url):
- name [string,required] - name of the collection.

Parameters (json):
- websocket_module [string,optional] - module of websocket pushes.
- badges_collection [string,optional] - collection of Badges to push badges to.
- badges_prefix [string,optional] - prefix of badges names.

Request example:

```json
{
    "websocket_module": "foobar"
}
```

Response example:

```json
{
    "status": true
}
```

### Create a record

`POST /record`

or

`POST /collection/{collection}/record`

Request parameters (json):
- collection [string,required] - name of the collection.
- recipient [string,required] - name of record recipient.
- sender [string,optional] - name of record author.
- thread [string,optional] - additional field for tagging record.
- title [string,optional] - title of record.
- text [string,optional] - text of record.
- image [string,optional] - image of record.
- payload [json,optional] - any JSON-serializable content.
- created_at [datetime,optional] - date of record creation.
- websocket_channel [string,optional] - centrifugo channel to push event to (if not equal to "recipient").
- badge_user [string,optional] - badge user to save badge for (if not equal to "recipient").

Request example:

```json
{
    "collection": "foobar",
    "recipient": "client1",
    "sender": "client2",
    "thread": "chat",
    "title": "Hello",
    "text": "World",
    "image": "https://example.com/image.jpg",
    "payload": {
        "foo": "bar"
    }
}
```

Response parameters (json):
- id [integer] - unique identity of inserted document.
- recipient [string] - name of record recipient.
- sender [string] - name of record author.
- thread [string] - additional field for tagging record.
- title [string] - title of record.
- text [string] - text of record.
- image [string] - image of record.
- payload [json] - any JSON-serializable content.
- created_at [datetime] - date of creation.

Response example:

```json
{
    "status": true,
    "content": {
        "record": {
            "id": 1,
            "recipient": "client1",
            "sender": "client2",
            "thread": "chat",
            "title": "Hello",
            "text": "World",
            "image": "https://example.com/image.jpg",
            "payload": {
                "foo": "bar"
            },
            "created_at": "2020-10-01 00:00:00"
        }
    }
}
```

If "websocket_module" is set for collection, then websocket push will be sent:

```json
{
    "module": "{{websocket_module}}",
    "event": "{{collection_name}}.record",
    "content": {
        "record": {
            "id": 1,
            "recipient": "client1",
            "sender": "client2",
            "thread": "chat",
            "title": "Hello",
            "text": "World",
            "image": "https://example.com/image.jpg",
            "payload": {
                "foo": "bar"
            },
            "created_at": "2020-10-01 00:00:00"
        }
    }
}
```

If "badges_collection" is set for collection, then badge will be saved:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record/{{ID of inserted record}}",
    "user": "client1"
}
```

### Get a record

`GET /record`

or

`GET /collection/{collection}/record/{id}`

Request parameters (json):
- collection [string,required] - name of the collection.
- id [integer,required] - the id of document.

Request example:

```json
{
    "collection": "foobar",
    "id": 1
}
```

Response parameters (json):
- id [integer] - unique identity of inserted document.
- recipient [string] - name of record recipient.
- sender [string] - name of record author.
- thread [string] - additional field for tagging record.
- title [string] - title of record.
- text [string] - text of record.
- image [string] - image of record.
- payload [json] - any JSON-serializable content.
- created_at [datetime] - date of creation.
- is_read [boolean] - whether record read or not.

Response example:

```json
{
    "status": true,
    "content": {
        "record": {
            "id": 1,
            "recipient": "client1",
            "sender": "client2",
            "thread": "chat",
            "title": "Hello",
            "text": "World",
            "image": "https://example.com/image.jpg",
            "payload": {
                "foo": "bar"
            },
            "is_read": false,
            "created_at": "2020-10-01 00:00:00"
        }
    }
}
```

### Delete a record

`DELETE /record`

or

`DELETE /collection/{collection}/record/{id}`

Request parameters (json or URL):
- collection [string,required] - name of the collection.
- id [integer,required] - the id of document.

Request example:

```json
{
    "collection": "foobar",
    "id": 1
}
```

Response example:

```json
{
    "status": true
}
```

If "badges_collection" is set for collection, then badge will be removed:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record/{{ID of read record}}",
    "user": "client1"
}
```

### Get records

`GET /records`

or

`GET /collection/{collection}/records`

Request parameters (json):
- collection [string,required] - name of the collection.
- recipient [string,required] - name of the recipient.
- sender [string,optional] - name of the sender.
- thread [string,optional] - name of the thread.
- search [string,optional] - searching string in "title" or "text".
- id [integer,optional] - the id of document to start from.
- order [string,optional] - type of ordering ("asc" or "desc"). Default is "desc".

Request example:

```json
{
    "collection": "foobar",
    "recipient": "client1"
}
```

Response parameters (json):
- id [integer] - unique identity of inserted document.
- recipient [string] - name of record recipient.
- sender [string] - name of record author.
- thread [string] - additional field for tagging record.
- title [string] - title of record.
- text [string] - text of record.
- image [string] - image of record.
- payload [json] - any JSON-serializable content.
- created_at [datetime] - date of creation.
- is_read [boolean] - whether record read or not.

Response example:

```json
{
    "status": true,
    "content": {
        "records": [
            {
                "id": 1,
                "recipient": "client1",
                "sender": "client2",
                "thread": "chat",
                "title": "Hello",
                "text": "World",
                "image": "https://example.com/image.jpg",
                "payload": {
                    "foo": "bar"
                },
                "is_read": false,
                "created_at": "2020-10-01 00:00:00"
            }
        ]
    }
}
```

### Post records

`POST /records`

Request parameters (json):
- collection [string,required] - name of the collection.
- thread [string,optional] - old name of the thread.
- new_thread [string,optional] - new name of the thread.

Request example:

```json
{
    "collection": "foobar",
    "thread": "chat1"
}
```

Response parameters (json):
- status [boolean] - request status.

Response example:

```json
{
    "status": true
}
```

### Read a record

`POST /record/read`

or

`POST /collection/{collection}/record/{id}/read`

Request parameters (json):
- id [integer,required] - id of the record.
- collection [string,required] - name of the collection.
- badge_user [string,optional] - badge user to remove badge from (if not equal to "recipient").

Request example:

```json
{
    "collection": "foobar",
    "id": 1
}
```

Response example:

```json
{
    "status": true
}
```

If "badges_collection" is set for collection, then badge will be removed:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record/{{ID of read record}}",
    "user": "client1"
}
```

### Unread a record

`POST /record/unread`

or

`POST /collection/{collection}/record/{id}/unread`

Request parameters (json):
- id [integer,required] - id of the record.
- collection [string,required] - name of the collection.
- badge_user [string,optional] - badge user to save badge for (if not equal to "recipient").

Request example:

```json
{
    "collection": "foobar",
    "id": 1
}
```

Response example:

```json
{
    "status": true
}
```

If "badges_collection" is set for collection, then badge will be saved:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record/{{ID of read record}}",
    "user": "client1"
}
```

### Read all records of recipient

`POST /records/read`

or

`POST /collection/{collection}/records/read`

Request parameters (json):
- recipient [string,required] - name of recipient.
- collection [string,required] - name of the collection.
- badge_user [string,optional] - badge user to remove badges from (if not equal to "recipient").

Request example:

```json
{
    "collection": "foobar",
    "recipient": "client1"
}
```

Response example:

```json
{
    "status": true
}
```

If "badges_collection" is set for collection, then badges will be removed:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record",
    "user": "client1"
}
```

### Delete all records of recipient

`DELETE /records`

or

`DELETE /collection/{collection}/records`

Request parameters (json):
- recipient [string,required] - name of recipient.
- collection [string,required] - name of the collection.
- badge_user [string,optional] - badge user to remove badges from (if not equal to "recipient").

Request example:

```json
{
    "collection": "foobar",
    "recipient": "client1"
}
```

Response example:

```json
{
    "status": true
}
```

If "badges_collection" is set for collection, then badges will be removed:

```json
{
    "collection": "{{badges_collection}}",
    "name": "{{badges_prefix if set}}}/{{collection_name}}/record",
    "user": "client1"
}
```

Contributors
============

- Ilyas Makashev [mehmatovec@gmail.com](mailto:mehmatovec@gmail.com)
- Temirlan Kasen [temirlankasen@gmail.com](mailto:temirlankasen@gmail.com)

Software
========

1. Ubuntu 16.04 Xenial
1. Nginx 1.16
1. PHP 7.4