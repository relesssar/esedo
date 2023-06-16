# PHP Library for smart bridge sb.egov.kz
EDS_TEMP_FILES

ENSI_SeGetDataGetItems

ESEDO_UNIVERSAL_SERVICE

---
Before to use this package install NCANode to sign envelop

`https://github.com/malikzh/NCANode`


### Install

`composer require relesssar/esedo`

### env


ESEDO_UNIVERSAL_SERVICE key
`SENDERPASS`

EDS_TEMP_FILES key
`SENDERPASS2`

ENSI_SeGetDataGetItems key
`SENDERPASS3`

### Usage
```php

...
$esedo = new \Relesssar\Esedo\Esedo('SHEPIP', 'ROUTEID', 'SENDERID', 'SENDERPASS', 'SENDERPASS2', 'SENDERPASS3');

// example

$envelop =  $esedo->esedo_doc_outgoing_env($data);

```