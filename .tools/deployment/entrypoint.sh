#!/bin/sh
echo "Starting entrypoint script..."

# Set root password if SSH_PASSWORD is provided
if [[ -n "$SSH_PASSWORD" ]]; then
  echo "root:$SSH_PASSWORD" | chpasswd
fi

# Generate host keys if not present
ssh-keygen -A

# Debugging: Show all files in /usr/local/bin
ls -l /usr/local/bin/

# Start the SSH service
service ssh start

# Debugging: Show if Apache is installed
apache2 -v

# Start Apache in the foreground (instead of using exec to allow debugging)
apache2-foreground