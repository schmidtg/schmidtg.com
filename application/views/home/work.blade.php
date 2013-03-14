@layout('master')

@section('content')

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
            	buildStartStop: false 
            });
		});
	</script>

@endsection
