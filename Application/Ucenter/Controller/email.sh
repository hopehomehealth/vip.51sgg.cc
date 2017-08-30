#!/bin/sh
#send report
from_name="51sggBrowser"
from="service@51sgg.cc"
to=$1
email_content=$2
if [ ! -n "$1" ]; then
    echo "1st email_content is empty!"
else
    email_subject="欢迎注册51sgg加速器"
    echo -e "To: ${to}\r\nFrom: \"${from_name}\" <${from}>\r\nSubject: ${email_subject}\r\nContent-type : text/html;charset=utf-8 \r\n\n  ${email_content}" | /usr/sbin/sendmail -t -ba
fi

