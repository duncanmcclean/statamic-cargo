---
title: "JSON API: Errors"
---

## Status Codes
Cargo uses conventional HTTP status codes to indicate the success or failure of a request. The most common status codes you'll encounter are:


| Code | Description                                                                                                                                 |
| ---- | ------------------------------------------------------------------------------------------------------------------------------------------- |
| 200  | Everything is ok!                                                                                                                           |
| 404  | The requested resource could not be found.<br><br>Cargo will return this status code when accessing the `/cart/*` endpoints without a cart. |
| 422  | The payload has missing required parameters or invalid data was given. Learn more about [validation errors](#validation-errors) below.      |

## Validation errors
When invalid data is submitted, Cargo will return a 422 status code with a similar looking response to this:

```json
{
	"message": "Invalid discount code",
	"errors": {
		"discount_code": ["Invalid discount code"]
	}
}
```