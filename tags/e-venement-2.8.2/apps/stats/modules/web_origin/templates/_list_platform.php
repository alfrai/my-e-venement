<?php
  $ua = parse_user_agent($web_origin->user_agent);
  echo $ua['platform'];
