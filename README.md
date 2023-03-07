# Custom PHP files

custom helper php files used in development process


### [Encrypter.php](./Encrypter.php)
- Encrypter class to encrypt and decrypt string with password
  - encrypt method to encrypt string
  - decrypt method to decrypt string

### [helper.php](./helper.php)
- Common helper functions
  - getIpAddress() to get the user ip
  - pluck() to pluck some columns from array
  - parseCSV() to pase a csv file
  - arrayToCsv() to convert array data to csv file and download it
  - validateColumnsExist() to validate if some columns exists in a dataset
  - arrayValue() to get value of an array property
  - sanitizeInput() to filter and sanitize input
  - encodeInput() to basic encode string
  - decodeInput() to basic decode string
  - generateCode() to generate code using str_pad function
  - isValidDate() to validate a date
  - formatDate() to format date
  - spellSeconds() to convert number of seconds into hours, minutes and seconds
  - formatAmount() to format a number with grouped thousands
  - displayAmount() to format a number with grouped thousands for display purpose
  - SpellAmount() to convert amount to word format
