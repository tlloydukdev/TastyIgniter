<?php namespace System\Console\Commands;

use Illuminate\Console\Command;
use Admin\Models\Menus_model as menuModel;
use DateTime;

class ToggleSpecials extends Command
{
    /**
     * The console command name.
     * @var string
     */
    protected $name = 'igniter:toggleSpecials';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Toggles pre-defined menu items based on day of the week.';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        
        $menuItemMap = [
            "1" => [ 141 ], // Monday
            "2" => [ 142, 143 ], // Tuesday
            "3" => [ 144, 145 ], // Wednesday
            "4" => [ 146, 118], // Thursday
            "5" => [ 136, 135, 134 ], // Friday
            "6" => [ ], // Saturday
            "7" => [ ], // Sunday
        ];
        $now = date('Y-m-d'); 
        $currentDate = new DateTime($now);
        $today = $currentDate->format('N');
        $todayName = $currentDate->format('l');
        $model = new menuModel;

        $this->output->writeln('<question>Toggling menu items for ' . $todayName . '...</question>');

        foreach($menuItemMap as $day => $items) {
            // First, set all rotating specials to disabled
            foreach($items as $itemId) {
                $this->output->writeln('<comment>Disabling menu item ' . $itemId . '...</comment>');
                $model->where("menu_id", $itemId)->update(array("menu_status" => false));
            }                  
        }
        foreach($menuItemMap as $day => $items) {
            // Next, only set todays specials to enabled
            if($day == $today) {
                foreach($items as $itemId) {
                    $this->output->writeln('<info>Enabling menu item ' . $itemId . '...</info>');
                    $model->where("menu_id", $itemId)->update(array("menu_status" => true));
                }
            }             
        }

    }
}
