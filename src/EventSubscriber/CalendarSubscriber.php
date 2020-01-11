<?php

namespace App\EventSubscriber;

use App\Controller\CoachController;
use App\Entity\Coach;
use CalendarBundle\Entity\Event;
use App\Repository\RdvRepository;
use CalendarBundle\CalendarEvents;
use App\Repository\CoachRepository;
use CalendarBundle\Event\CalendarEvent;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{

   
    private $repo;
    private $id;
   

    public function __construct(RdvRepository $repo)
    {
        
        $this->repo = $repo;
        
    }

    public function setId($id) {
        $this->id = $id;
    }
    
    public static function getSubscribedEvents()
    {
        return [
            CalendarEvents::SET_DATA => 'onCalendarSetData',
        ];
    }

    public function onCalendarSetData(CalendarEvent $calendar)
    {   
        
        $start = $calendar->getStart();
        $end = $calendar->getEnd();
        $filters = $calendar->getFilters();

        $url = $_SERVER['HTTP_REFERER'];
        $x = explode('/',$url);
        $client_id = end($x);

        // You may want to make a custom query from your database to fill the calendar

        $rdvs = $this->repo->findBy(['coach'=>$client_id]);
        
        foreach($rdvs as $rdv){
            $date = date_format($rdv->getHeure(), 'H:i') . ' Indisponible';
            $calendar->addEvent( $booking = new Event(
                $date,
                $rdv->getJour()
            ));
            $booking->setOptions(['backgroundColor'=>'red', 'borderColor'=>'red']);
            
        }
    }
}