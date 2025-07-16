#!/bin/sh
echo "Starting entrypoint script..."

# Set root password if SSH_PASSWORD is provided
if [ -n "$SSH_PASSWORD" ]; then
  echo "root:$SSH_PASSWORD" | chpasswd
fi

# Generate SSH host keys if not already present
ssh-keygen -A

# Start SSH service
service ssh start

# Start Apache in the foreground to keep the container running
exec apache2-foreground
