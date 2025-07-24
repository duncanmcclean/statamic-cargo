---
title: "JSON API: Errors"
---

## Status Codes
Cargo uses conventional HTTP status codes to indicate the success or failure of a request. The most common status codes you'll encounter are:


| Status                                                                                            | Description                                                                                                                                 |
|---------------------------------------------------------------------------------------------------| ------------------------------------------------------------------------------------------------------------------------------------------- |
| <span class="font-mono px-[5px] rounded-[4px] bg-code-insert-bg text-code-insert-text">200</span> | Everything is ok!                                                                                                                           |
| <span class="font-mono px-[5px] rounded-[4px] bg-code-delete-bg text-code-delete-text">400</span> | The requested resource could not be found.<br><br>Cargo will return this status code when accessing the `/cart/*` endpoints without a cart. |
| <span class="font-mono px-[5px] rounded-[4px] bg-code-delete-bg text-code-delete-text">422</span> | The payload has missing required parameters or invalid data was given. Learn more about [validation errors](#validation-errors) below.      |

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