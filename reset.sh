chown -R nginx *
chgrp -R nginx *

rm -rf App/Runtime/common~runtime.php 
rm -rf App/Runtime/Cache/* 
rm -rf App/Runtime/Data/*
rm -rf App/Runtime/Temp/*

