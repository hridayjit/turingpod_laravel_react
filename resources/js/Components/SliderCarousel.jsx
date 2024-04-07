import Slider from 'react-slick';
import "slick-carousel/slick/slick.css";
import "slick-carousel/slick/slick-theme.css";
import CardComponent from './CardComponent';

function SliderCarousel (props) {
    return (
        <Slider {...props.settings}>
            {props.contents.map((content) => (
                <CardComponent title={content.title} url={content.url} description={content.description} link={content.link} height={props.settings.height} ></CardComponent>
            ))}
        </Slider>
    );
}

export default SliderCarousel;