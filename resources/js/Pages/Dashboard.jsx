import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Content from './Content';
import { Head } from '@inertiajs/react';
import CardComponent from '@/Components/CardComponent';
// import Carousel from 'react-bootstrap/Carousel';
import SliderCarousel from '@/Components/SliderCarousel';

export default function Dashboard({ auth }) {
    let contents = [
        {title:'Tensile Property Evaluator', url: 'https://www.collinsdictionary.com/images/full/card_199913294.jpg', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('tensile_property_evaluator')},
        {title:'Smart Statistics', url: 'https://upload.wikimedia.org/wikipedia/commons/5/58/AcetoFive.JPG', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('smart_statistics')},
        {title:'Solar System Simulation', url: 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ8Z-zoVpMjPon2RlNysGCEwfW8ldSC7volTjlOnoE_JUK-uyvZb2NcJVzOJ4xKNYzGpisOpjjO-F1pkoErIU6CnT44k_WlRPpIOOGCYw', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('solar_system_simulation')},
        {title:'Momentum Conservation', url: 'https://miro.medium.com/v2/resize:fit:1400/1*iZpPifh-0-vuc-35Sw5k6Q.png', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('momentum_conservation')},
        {title:'Chat App', url: 'https://img.freepik.com/premium-vector/chat-app-logo-design-template-can-be-used-icon-chat-application-logo_605910-1724.jpg', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('chat_app')},
        {title:'Blogs', url: 'https://www.ryrob.com/wp-content/uploads/2021/11/iStock-496848472-1024x1024.jpg', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('blogs')},
        {title:'Query and Recommend', url: 'https://i0.wp.com/ottopress.com/files/2010/11/query.jpg?resize=317%2C421', description: 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industrys standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book', link: route('query_recommend')},

    ];
    let settings = {
        dots: true,
        infinite: true,
        speed: 500,
        slidesToShow: 4,
        slidesToScroll: 2,
        height: '200px'
      };
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Products ...</h2>}
        >

            <Head title="Dashboard" />

            <div className="p-2">
                <SliderCarousel settings={settings} contents={contents}></SliderCarousel>
            </div>
            
            
            
            <div className="p-2">
                
            </div>

            <div className="p-2 d-flex" style={{justifyContent: 'center', alignItems: 'center'}}>
                <div>

                </div>
            </div>
            

            {/* <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                        <div className="p-6 text-gray-900"><Content data={auth.user}/></div>
                    </div>
                </div>
            </div> */}
        </AuthenticatedLayout>
    );
}
