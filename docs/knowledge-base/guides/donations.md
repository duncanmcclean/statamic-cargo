---
title: Handling donations
---

The easiest way to handle donations is by creating a £1 product, and incrementing the quantity as needed. Like this:

::tabs
::tab antlers
```antlers
{{ cart:add }}  
	<label for="quantity">Total</label>
    <input type="number" name="quantity" min="1" required>  
    
    <button>Donate</button>  
{{ /cart:add }}
``` 
::tab blade
```blade
<s:cart:add>  
	<label for="quantity">Total</label>
    <input type="number" name="quantity" min="1" required>  
    
    <button>Donate</button>  
</s:cart:add>
``` 
::