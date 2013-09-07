CS Cart PayU TR
======================================

What is this?
--------------
This mod integrates the PayU TR payment processor with CS Cart version 3.x  


How to Install?
---------------

1) Copy the files onto the root of your CS Cart installation  
2) Go to the following URL (replace mystore with your store URL address)

http://mystore/index.php?dispatch=payment_notification.install&payment=payu_tr

A message should appear informing you that installation is complete. 

How to Configure?
-----------------

In your store backend (admin.php):    
1) Go to Administration > Payment Methods. Click 'Add Payment'    
2) Type 'PayU TR' (or anything else you want) for Name    
3) Select 'PayU TR' for Processor    
4) Select 'Internet Payments' for Category   
5) Click the 'Configure' tab (next to general) and provide your PayU TR Secure Hash and Gateway ID. You can find both of them in Merchant Portal (In the portal, click 'Gateways' then click 'API and Logo')    
6) Click the 'Create' Button.   
7) Clear your CSCart cache by accessing this URL: http://mystore/admin.php?cc 
(replace 'mystore' with your store URL address and replace admin.php with your admin script filename)

And you're done!

LICENSE 
-------

Copyright &copy; 2013, Yasin Kuyu @yasinkuyu
All rights reserved.

