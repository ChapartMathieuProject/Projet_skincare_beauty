
printenv | grep -v "no_proxy" > /etc/environment
exec cron -f
