#!/bin/sh
# get the input parameters
found=0;
name=
for item in $@ ;
do
	if [[ $found == 1 ]]; then
		found=0;
		if [ "$name" == "-startdate" ];then
			STARTDATE=$item;
		fi
		if [ "$name" == "-today" ];then
			TODAY=1;
		fi
		if [ "$name" == "-enddate" ];then
			ENDDATE=$item;
		fi
	fi		
	if [[ "$item" == "-startdate" || "$item" == "-enddate" || "$item" == "-today" ]]; then
		name=$item;
		found=1;
	fi
done
# Calculating the time difference
if [[ "x"$STARTDATE == "x" && "x"$TODAY != "x1" ]];then
	STARTDATE=`date -d '-3 day' +%Y%m%d`;
fi
if [[ "x"$ENDDATE == "x" && "x"$TODAY != "x1" ]];then
	ENDDATE=`date +%Y%m%d`;
fi
TIMEDIFFERENCE=$((ENDDATE-STARTDATE+1));
if [ "x"$TODAY == "x1" ];then
	TIMEDIFFERENCE=1;
fi

cd App/Runtime/Logs/Home/

for((i=0;i<$TIMEDIFFERENCE;i++))
do
	#echo "i:"$i;
	STARTDATE=`date +%Y%m%d -d "$i day ago"`;
#	ENDDATE=`date +%Y%m%d -d "$((i-1)) day ago"`;
	daily_date=`date +%Y_%m_%d -d "$i day ago"`;
	log_name=${daily_date:2}".log";
	#echo $STARTDATE"-"$ENDDATE;
	#echo $log_name"-"$daily_date;
	# open file 
    
	# get data
	arena_id=`grep "a=arena_info" *${log_name} | awk -F 'arenaid=' '{print $2}'| awk -F '&' '{print $1}'| awk -F ' ' '{print $1}'| sort | uniq`;
	#echo $arena_id;
	for j in $arena_id
	do  
  	#echo "j:"$j;
    	#exit;
	JOIN_QUEUE=`grep "a=join_queue" *${log_name}| grep "arenaid=$j"| grep "ret" |wc -l`
	JOIN_QUEUE_ERROR=`grep "a=join_queue" *${log_name}| grep "arenaid=$j"| grep "ret" | grep -v "ret:0"|wc -l`
	LEAVE_QUEUE=`grep "a=leave_queue" *${log_name}| grep "arenaid=$j"| grep "ret" |wc -l`
	TIMEOUT=`grep timeout *${log_name} | grep "arenaid:$j"| awk -F 'number:' '{print $2}'|awk -F ',' '{a += $1}'END'{print a}'`

	URL="http://localhost/api.php?m=Arena&a=stat_arena&arenaid=$j";
	if [ "x"$TIMEOUT == "x" ];then
        	TIMEOUT=0
	fi
	if [[ "x"$STARTDATE != "x" && "x"$ENDDATE != "x" && "x"$TODAY == "x" ]];then
        	URL+="&startdate=${STARTDATE}&enddate=${STARTDATE}";
	fi
	if [ "x"$TODAY == "x1" ];then
		URL+="&today=${TODAY}";
	fi
	URL+="&join_queue_nums=${JOIN_QUEUE}&join_queue_error=${JOIN_QUEUE_ERROR}&leave_queue=${LEAVE_QUEUE}&timeout=${TIMEOUT}";
	echo ${URL};
	STR=`curl $URL`;
	#STR=`curl http://localhost/api.php\?m=Arena\&a=stat_arena\&arenaid=1\&startdate=20150116\&enddate=20150116\&join_queue_nums=0\&join_queue_error=0\&leave_queue=0\&timeout=0`;
	echo ${STR};
	done
done
