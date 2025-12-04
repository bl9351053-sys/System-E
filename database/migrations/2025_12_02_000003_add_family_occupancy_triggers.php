<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_insert`;');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `sys_families_after_insert`
            AFTER INSERT ON `families`
            FOR EACH ROW
            BEGIN
                IF NEW.evacuation_area_id IS NOT NULL AND NEW.status = "evacuated" THEN
                    UPDATE evacuation_areas SET current_occupancy = current_occupancy + NEW.total_members WHERE id = NEW.evacuation_area_id;
                END IF;
            END;
            SQL
        );

        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_update`;');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `sys_families_after_update`
            AFTER UPDATE ON `families`
            FOR EACH ROW
            BEGIN
                IF OLD.evacuation_area_id = NEW.evacuation_area_id THEN
                    IF OLD.status = "evacuated" AND NEW.status != "evacuated" THEN
                        UPDATE evacuation_areas SET current_occupancy = current_occupancy - OLD.total_members WHERE id = OLD.evacuation_area_id;
                    ELSEIF OLD.status != "evacuated" AND NEW.status = "evacuated" THEN
                        UPDATE evacuation_areas SET current_occupancy = current_occupancy + NEW.total_members WHERE id = NEW.evacuation_area_id;
                    ELSEIF OLD.status = "evacuated" AND NEW.status = "evacuated" AND OLD.total_members != NEW.total_members THEN
                        UPDATE evacuation_areas SET current_occupancy = current_occupancy + (NEW.total_members - OLD.total_members) WHERE id = NEW.evacuation_area_id;
                    END IF;
                ELSE
                    IF OLD.status = "evacuated" AND OLD.evacuation_area_id IS NOT NULL THEN
                        UPDATE evacuation_areas SET current_occupancy = current_occupancy - OLD.total_members WHERE id = OLD.evacuation_area_id;
                    END IF;
                    IF NEW.status = "evacuated" AND NEW.evacuation_area_id IS NOT NULL THEN
                        UPDATE evacuation_areas SET current_occupancy = current_occupancy + NEW.total_members WHERE id = NEW.evacuation_area_id;
                    END IF;
                END IF;
            END;
            SQL
        );

        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_delete`;');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER `sys_families_after_delete`
            AFTER DELETE ON `families`
            FOR EACH ROW
            BEGIN
                IF OLD.evacuation_area_id IS NOT NULL AND OLD.status = "evacuated" THEN
                    UPDATE evacuation_areas SET current_occupancy = current_occupancy - OLD.total_members WHERE id = OLD.evacuation_area_id;
                END IF;
            END;
            SQL
        );
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_insert`;');
        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_update`;');
        DB::unprepared('DROP TRIGGER IF EXISTS `sys_families_after_delete`;');
    }
};
