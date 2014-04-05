<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	<title>Graham Schmidt - Software Engineer</title>
	<meta name="viewport" content="width=device-width">
	{{ HTML::style('/css/base.css') }}
	{{ HTML::style('/css/layout.css') }}
	{{ HTML::style('/css/skeleton.css') }}
	{{ HTML::style('/css/styles.css') }}
<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-39490804-1']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
	
</head>
<body>

<header>
    <nav>
        <ul>
            <li>
                <a href="/">Home</a>
            </li>
            <!--
            <li>
                <a href="/work">Work</a>
            </li>
            <li>
                <a href="/blog">Blog</a>
            </li>
            -->
            <li>
                <a href="/about">About</a>
            </li>
            <li>
                <a href="/resume">Resume</a>
            </li>
        </ul>
    </nav>
    <a href="/">
    <div class="logo"></div>
    </a>

    <h1>Hi, I'm Graham</h1>
    <p>I work as a full-time software engineer for <a href="http://www.sermo.com/">Sermo</a>. I’m passionate about web technologies, love craft beer, and play a mean bagpipe.</p>
    <img src="/img/graham-schmidt-software-engineer.jpg" alt="Graham Schmidt - Software Engineer" class="profile shadow" width="189" height="187" />
</header>

@yield('content')

<footer>
    <nav>
        <ul>
            <li>
                <a href="/">Home</a>
            </li>
            <!--
            <li>
                <a href="/work">Work</a>
            </li>
            <li>
                <a href="/blog">Blog</a>
            </li>
            <li>
                <a href="/about">About</a>
            </li>
            -->
            <li>
                <a href="/about">About</a>
            </li>
            <li>
                <a href="/resume">Resume</a>
            </li>
        </ul>
    </nav>
</footer>

@yield('scripts')

</body>
</html>
