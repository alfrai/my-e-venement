Gauges API
===========

These endpoints will allow you to manage gauges. Base URI is '/api/v2/manifestations/{mid}/gauges'.

Gauges API response structure
------------------------------

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the gauge                                                          |
+------------------+--------------------------------------------------------------------------+
| name             | Name of the current Gauge (through its Workspace)                        |
+------------------+--------------------------------------------------------------------------+
| metaGaugeId      | Id of the Meta Gauge (its Workspace id)                                  |
+------------------+--------------------------------------------------------------------------+
| availableUnits   | The available space in this gauge                                        |
|                  | To avoid information leaks, if more space is available than the maximum  |
|                  | configured, the maximum is exposed instead of the really available space |
+------------------+--------------------------------------------------------------------------+
| totalUnits       | *Optional* The size of the gauge                                         |
+------------------+--------------------------------------------------------------------------+
| prices           | Collection of Prices                                                     |
+------------------+--------------------------------------------------------------------------+
| createdAt        | *Optional* Datetime of creation                                          |
|                  | `ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`_     |
+------------------+--------------------------------------------------------------------------+
| updatedAt        | *Optional* Datetime of last update                                       |
|                  | `ISO 8601 Extended Format <https://fr.wikipedia.org/wiki/ISO_8601>`_     |
+------------------+--------------------------------------------------------------------------+

Gauges API response structure
------------------------------

When you get a collection of resources, "Default" serialization group will be used and the following fields will be exposed:

+------------------+--------------------------------------------------------------------------+
| Field            | Description                                                              |
+==================+==========================================================================+
| id               | Id of the price                                                          |
+------------------+--------------------------------------------------------------------------+
| translations     | Collection of translations                                               |
+------------------+--------------------------------------------------------------------------+
| prices           | Collection of prices available from this gauge                           |
+------------------+--------------------------------------------------------------------------+

Available actions to interact with a gauge
-------------------------------------------

+------------------+-----------------------------------------------+
| Action           | Description                                   |
+==================+===============================================+
| List             | Getting a list of gauges from a manifestation |
+------------------+-----------------------------------------------+
| Show             | Getting a single gauge                        |
+------------------+-----------------------------------------------+
| Update           | Update a single gauge                         |
+------------------+-----------------------------------------------+
| Create           | Create a single gauge                         |
+------------------+-----------------------------------------------+
| Delete           | Delete a single gauge                         |
+------------------+-----------------------------------------------+


Getting a collection of gauges
---------------------------------------

To retrieve the full gauge list, you will need to call the /api/v2/manifestations/{mid}/gauges endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/manifestations/{mid}/gauges

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/20/gauges \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

    {
        "page": 1,
        "limit": 10,
        "pages": 1,
        "total": 1,
        "_links": {
            "self": {
                "href": "\/tck.php\/osApiGauges?limit=10"
            },
            "first": {
                "href": "\/tck.php\/osApiGauges?limit=10&page=1"
            },
            "last": {
                "href": "\/tck.php\/osApiGauges?limit=10&page=1"
            },
            "next": {
                "href": "\/tck.php\/osApiGauges?limit=10&page=1"
            }
        },
        "_embedded": {
            "items": [
                {
                    "id": 20,
                    "name": "Semaine des ambassadeurs 2017",
                    "metaGaugeId": 1,
                    "availableUnits": 50,
                    "total": 50,
                    "manifestationId": 20,
                    "_link[manifestation]": null,
                    "_link": {
                        "manifestation": "\/api\/v2\/manifestations\/20"
                    }
                }
            ]
        }
    }

Getting a single gauge
-----------------------

To retrieve the detail of a single gauge you will need to call the /api/v2/manifestations/{mid}/gauges/{id} endpoint with the GET method.

Definition
^^^^^^^^^^

.. code-block:: text

    GET /api/v2/manifestations/{mid}/gauges/{id}

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/20/gauges/20 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X GET

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

  {
      "id": 20,
      "name": "Semaine des ambassadeurs 2017",
      "metaGaugeId": 1,
      "availableUnits": 50,
      "total": 50,
      "manifestationId": 20,
      "_link[manifestation]": null,
      "_link": {
          "manifestation": "\/api\/v2\/manifestations\/20"
      }
  }
  
Update a single gauge
----------------------

To update a single gauge you will need to call the /api/v2/manifestations/{mid}/gauges/{id} endpoint with the POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/manifestations/{mid}/gauges/{id}

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/20/gauges/20 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '{
          "total":59
        }'

Sample Response
^^^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 200 OK

.. code-block:: json

  {
      "id": 20,
      "name": "Semaine des ambassadeurs 2017",
      "metaGaugeId": 1,
      "availableUnits": 59,
      "total": 59,
      "manifestationId": 20,
      "_link[manifestation]": null,
      "_link": {
          "manifestation": "\/api\/v2\/manifestations\/20"
      }
  }
  
Create a single gauge
----------------------

To create a single gauge you will need to call the /api/v2/manifestations/{mid}/gauges endpoint with the POST method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/manifestations/{mid}/gauges

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/20/gauges \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X POST \
        --data '{
            "metaGaugeId":3,
            "total":42,
            "manifestationId":20
        }'

Sample Response
^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 201 CREATED

.. code-block:: json

  {
      "id": 20,
      "name": "Semaine des ambassadeurs 2017",
      "metaGaugeId": 1,
      "availableUnits": 59,
      "total": 59,
      "manifestationId": 20,
      "_link[manifestation]": null,
      "_link": {
          "manifestation": "\/api\/v2\/manifestations\/20"
      }
  }
  
Delete a single gauge
----------------------

To delete a single gauge you will need to call the /api/v2/manifestations/{mid}/gauges/{id} endpoint with the DELETE method.

Definition
^^^^^^^^^^

.. code-block:: text

    POST /api/v2/manifestations/{mid}/gauges/{id}

Example
^^^^^^^

.. code-block:: bash

    $ curl http://e-venement.local/api/v2/manifestations/20/gauges/20 \
        -H "Authorization: Bearer SampleToken" \
        -H "Content-Type: application/json" \
        -X DELETE

Sample Response
^^^^^^^^^^^^^^^^

.. code-block:: text

    STATUS: 204 DELETED

.. code-block:: json

