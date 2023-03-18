pid=$(cat run.pid)

case $1 in
	start)
		echo 'Start Pinga...'
		php start-swoole-http.php
		;;
	stop)
		echo 'Stop Pinga...'
		kill -15 $pid
		;;
	reload)
		echo 'Reload Pinga...'
		kill -USR1 $pid
		;;
	retask)
		echo 'Reload Task...'
		kill -USR2 $pid
		;;
	restart)
		echo 'Stoping Pinga...'
		kill -15 $pid
		sleep 2
		echo 'Start Pinga...'
		php start-swoole-http.php
		;;
	status)
		ps -f $pid
		;;
esac
