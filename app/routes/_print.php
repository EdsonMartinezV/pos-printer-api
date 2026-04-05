<?php

app()->post('/print', 'PrintersController@print');
app()->get('/printer-name', 'PrintersController@getPrinterName');