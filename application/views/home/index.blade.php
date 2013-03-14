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
</head>
<body>

<header>
    <div class="logo"></div>
    <h1>Hi, I'm Graham</h1>
    <p>I work as a full-time software engineer for <a href="http://www.nutraclick.com/">NutraClick</a>. I’m passionate about web technologies, love craft beer, and play a mean bagpipe.</p>
    <img src="/img/graham-schmidt-software-engineer.jpg" alt="Graham Schmidt - Software Engineer" class="profile shadow" width="189" height="187" />
</header>

<section class="projects container">
    <h2><a href="http://www.eventry.net/">Eventry.net</a></h2>
    <p>Technologies: PHP, Javascript, MySQL, HTML/CSS</p>

    <figure>
        <ul id="slider">
            <li>
                <img src="/img/projects/eventry/1-eventry-home.png" alt="Eventry.net - Home screen" />
                <figcaption>
                    Login Screen
                </figcaption>
            </li>
            <li>
                <img src="/img/projects/eventry/2-eventry-choose.png" alt="Eventry.net - Choose screen" />
                <figcaption>
                    Choose Screen
                </figcaption>
            </li>
            <li>
                <img src="/img/projects/eventry/3-eventry-form.png" alt="Eventry.net - Form screen" />
                <figcaption>
                    Form Screen
                </figcaption>
            </li>
            <li>
                <img src="/img/projects/eventry/4-eventry-confirm.png" alt="Eventry.net - Confirmation screen" />
                <figcaption>
                    Confirmation Screen
                </figcaption>
            </li>
            <li>
                <img src="/img/projects/eventry/5-eventry-dash.png" alt="Eventry.net - Dashboard screen" />
                <figcaption>
                    Dashboard Screen
                </figcaption>
            </li>
        </ul>
    </figure>

    <p>&nbsp;</p>
    <p>&nbsp;</p>

</section>

<footer>
</footer>

	{{ HTML::script(URL::$base.'/js/modernizr-2.5.3.min.js') }}
	{{ HTML::script('http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js') }}
	
	<!-- Anything Slider -->
	{{ HTML::style('/bundles/AnythingSlider/css/theme-schmidtg.css') }}
	{{ HTML::script(URL::$base.'/bundles/AnythingSlider/js/jquery.anythingslider.min.js') }}

	<!-- AnythingSlider initialization -->
	<script>
		// DOM Ready
		$(function(){
			$('#slider').anythingSlider({
            	buildStartStop: false 
            });
		});
	</script>


</body>
</html>
