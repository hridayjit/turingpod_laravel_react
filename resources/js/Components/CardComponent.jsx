import Button from 'react-bootstrap/Button';
import Card from 'react-bootstrap/Card';

function CardComponent(props) {
  return (
    <Card style={{ width: '98%', marginLeft: '0%;', marginRight: '2%' }}>
      <Card.Img variant="top" src={props.url} style={{height: props.height}} />
      <Card.Body>
        <Card.Title><p><b>{props.title}</b></p></Card.Title>
        <Card.Text>
          <p style={{fontSize: '10px'}}>{props.description}</p>
        </Card.Text>
        <a href={props.link}><Button variant="primary" className="btn-primary">Proceed</Button></a>
      </Card.Body>
    </Card>
  );
}

export default CardComponent;