<?php

class Roomcard
{
    private int $roomId;
    private string $roomTitle;
    private string $roomImage;
    private float $roomPrice;
    private int $roomSeats;
    private int $roomProjectors;

    public function __construct($roomId, $roomTitle, $roomImage, $roomPrice, $roomSeats, $roomProjectors)
    {
        $this->roomId = $roomId;
        $this->roomTitle = $roomTitle;
        $this->roomImage = $roomImage;
        $this->roomPrice = $roomPrice;
        $this->roomSeats = $roomSeats;
        $this->roomProjectors = $roomProjectors;
    }

    public function display()
    {
        return ("
        <div class=\"room-card backdrop-blur-lg bg-gray-800/50 rounded-xl overflow-hidden border border-gray-700 transform transition-all duration-300 hover:-translate-y-1 hover:border-violet-500\">
            <div class=\"relative\">
                <img src=\"" . $this->roomImage . "\" alt=\"Room Image\" class=\"w-full h-48 object-cover transition-transform duration-300 group-hover:scale-105\">
                <div class=\"absolute inset-0 bg-gradient-to-t from-gray-900/90 to-transparent\"></div>
            </div>
            
            <div class=\"p-6 space-y-4\">
                <h3 class=\"text-xl font-semibold text-violet-300\">" . $this->roomTitle . "</h3>
                
                <div class=\"flex justify-between text-gray-300\">
                    <span class=\"flex items-center space-x-2\">
                        <i class=\"fas fa-users text-violet-400\"></i>
                        <span>" . $this->roomSeats . " Seats</span>
                    </span>
                    <span class=\"flex items-center space-x-2\">
                        <i class=\"fas fa-chalkboard-teacher text-violet-400\"></i>
                        <span>" . $this->roomProjectors . " Projector</span>
                    </span>
                </div>

                <div class=\"flex items-center justify-between\">
                    <div class=\"text-lg font-bold text-gray-200\">
                        <span>$" . $this->roomPrice . "</span>
                        <span class=\"text-sm text-gray-400\">/hour</span>
                    </div>
                    <a href=\"roomview.php?id=" . $this->roomId . "\" 
                       class=\"inline-flex items-center px-4 py-2 bg-gradient-to-r from-violet-600 to-purple-600 text-white font-semibold rounded-lg hover:from-violet-500 hover:to-purple-500 transition-all duration-300\">
                        Book Now
                    </a>
                </div>
            </div>
        </div>
        ");
    }
}

?>