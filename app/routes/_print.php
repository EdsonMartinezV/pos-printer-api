<?php

app()->post('/print', 'PrintersController@print');
app()->post('/test-print', 'PrintersController@testPrint');
app()->get('/printer-name', 'PrintersController@getPrinterName');