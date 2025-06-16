---
title: "JSON API: Validation"
---


Cargo will report a 422 status code when invalid data is submitted in a request. The response body will look like this:

```json
{
	"message": "whatevs",
	"errors": {
		"thing": ["Message"]
	}
}
```