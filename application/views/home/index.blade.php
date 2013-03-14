@layout('master')

@section('content')

<section class="projects container">

    <figure>
        <ul id="slider">
            <li>
                <h2><a href="http://www.eventry.net/">Project: Eventry.net</a></h2>
                <p>Technologies: Codeigniter, PHP, Javascript, jQuery, MySQL, HTML/CSS, Photoshop</p>
                
                <a href="http://www.eventry.net" target="_blank">
                    <img src="/img/projects/eventry/eventry.net.png" alt="Eventry.net - Home screen" />
                </a>
                <figcaption>
                    Login Screen
                </figcaption>
            </li>
            <li>
                <h2><a href="http://www.saskhighland.ca/">Project: Saskhighland.ca</a></h2>
                <p>Technologies: Laravel, PHP, Javascript, MySQL, HTML/CSS, Photoshop</p>

                <a href="http://www.saskhighland.ca/">
                    <img src="/img/projects/saskhighland/saskhighland.ca.png" alt="Saskatchewan Highland Gathering &amp; Celtic Gathering" />
                </a>
                <figcaption>
                    Choose Screen
                </figcaption>
            </li>
            <li>
                <h2><a href="http://www.schmidtg.com/projects/nextlex">Project: Lexpress</a></h2>
                <p>Technologies: Google Maps API, PHP, Javascript, jQuery Mobile, MySQL, HTML/CSS, Photoshop</p>

                <a href="http://www.schmidtg.com/projects/nextlex">
                    <img src="/img/projects/eventry/3-eventry-form.png" alt="Eventry.net - Form screen" />
                </a>
                <figcaption>
                    Form Screen
                </figcaption>
            </li>
            <li>
                <h2><a href="http://www.cmapllc.com/">Project: CMAP LLC</a></h2>
                <p>Technologies: Wordpress, PHP, Javascript, MySQL, HTML/CSS, Photoshop</p>

                <a href="http://www.cmapllc.com" target="_blank">
                    <img src="/img/projects/cmap/cmapllc.com.png" alt="Cmap LLC" />
                </a>
                <figcaption>
                    Confirmation Screen
                </figcaption>
            </li>
            <li>
                <h2><a href="http://www.winnipegscottishfestival.org/">Project: Winnipeg Scottish Festival</a></h2>
                <p>Technologies: HTML/CSS, Javascript, Photoshop</p>

                <a href="http://www.winnipegscottishfestival.org" target="_blank">
                    <img src="/img/projects/wsf/winnipeg-scottish-festival.png" alt="Winnipeg Scottish Festival" />
                </a>
                <figcaption>
                    Dashboard Screen
                </figcaption>
            </li>
        </ul>
    </figure>

    <p>&nbsp;</p>
    <p>&nbsp;</p>

</section>

@endsection


@section('scripts')

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
            	buildStartStop: false,
            	autoPlay: true 
            });
		});
	</script>

@endsection
