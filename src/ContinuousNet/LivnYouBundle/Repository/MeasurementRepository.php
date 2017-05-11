<?php

namespace ContinuousNet\LivnYouBundle\Repository;

use ContinuousNet\LivnYouBundle\Repository\Join\MeasurementJoin;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class MeasurementRepository
 * @package ContinuousNet\LivnYouBundle\Repository
 */
class MeasurementRepository extends BaseRepository
{
    protected $serchableFields = ['measurement.firstName', 'measurement.lastName', 'measurement.groupName', 'measurement.address', 'measurement.city', 'measurement.zipCode', 'measurement.state', 'measurement.mobileNumber', 'measurement.email', 'measurement.phone', 'measurement.appName', 'measurement.appVersion', 'measurement.dataReceived', 'measurement.fmHcPcZaMaxColor', 'measurement.fmHcPcZbMaxColor', 'measurement.fmHcPcZcMaxColor', 'measurement.fmHcPcZdMaxColor', 'measurement.fmHcPcZeMaxColor', 'measurement.fmHcPcZfMaxColor', 'measurement.ffwPcZaMaxColor', 'measurement.ffwPcZbMaxColor', 'measurement.ffwPcZcMaxColor', 'measurement.ffwPcZdMaxColor', 'measurement.ffwPcZeMaxColor', 'measurement.ffwPcZfMaxColor', 'measurement.ffwPcZgMaxColor', 'measurement.mmhiZaMaxColor', 'measurement.mmhiZbMaxColor', 'measurement.mmhiZcMaxColor', 'measurement.mmhiZdMaxColor', 'measurement.adcrZaMaxColor', 'measurement.adcrZbMaxColor', 'measurement.adcrZcMaxColor', 'measurement.adcrZdMaxColor', 'measurement.adcrZeMaxColor', 'measurement.asmmiZaMaxColor', 'measurement.asmmiZbMaxColor', 'measurement.asmmiZcMaxColor', 'measurement.asmmiZdMaxColor', 'measurement.ecwPcZaMaxColor', 'measurement.ecwPcZbMaxColor', 'measurement.ecwPcZcMaxColor', 'measurement.ecwPcZdMaxColor', 'measurement.ecwPcZeMaxColor', 'measurement.ecwPcZfMaxColor', 'measurement.ecwPcZgMaxColor', 'measurement.icwPcZaMaxColor', 'measurement.icwPcZbMaxColor', 'measurement.icwPcZcMaxColor', 'measurement.icwPcZdMaxColor', 'measurement.icwPcZeMaxColor', 'measurement.icwPcZfMaxColor', 'measurement.icwPcZgMaxColor', 'measurement.fmPcZaMaxColor', 'measurement.fmPcZbMaxColor', 'measurement.fmPcZcMaxColor', 'measurement.fmPcZdMaxColor', 'measurement.fmPcZeMaxColor', 'measurement.fmPcZfMaxColor', 'measurement.tbwffmPcZaMaxColor', 'measurement.tbwffmPcZbMaxColor', 'measurement.tbwffmPcZcMaxColor', 'measurement.tbwffmPcZdMaxColor', 'measurement.tbwffmPcZeMaxColor', 'measurement.tbwffmPcZfMaxColor', 'measurement.tbwffmPcZgMaxColor', 'measurement.dffmiZaMaxColor', 'measurement.dffmiZbMaxColor', 'measurement.dffmiZcMaxColor', 'measurement.dffmiZdMaxColor', 'measurement.mpMetaiZaMaxColor', 'measurement.mpMetaiZbMaxColor', 'measurement.mpMetaiZcMaxColor', 'measurement.mpMetaiZdMaxColor', 'measurement.iffmiZaMaxColor', 'measurement.iffmiZbMaxColor', 'measurement.iffmiZcMaxColor', 'measurement.iffmiZdMaxColor', 'measurement.bmriZaMaxColor', 'measurement.bmriZbMaxColor', 'measurement.bmriZcMaxColor', 'measurement.bmriZdMaxColor', 'measurement.ffecwPcZaMaxColor', 'measurement.ffecwPcZbMaxColor', 'measurement.ffecwPcZcMaxColor', 'measurement.ffecwPcZdMaxColor', 'measurement.ffecwPcZeMaxColor', 'measurement.ffecwPcZfMaxColor', 'measurement.ffecwPcZgMaxColor', 'measurement.fficwPcZaMaxColor', 'measurement.fficwPcZbMaxColor', 'measurement.fficwPcZcMaxColor', 'measurement.fficwPcZdMaxColor', 'measurement.fficwPcZeMaxColor', 'measurement.fficwPcZfMaxColor', 'measurement.fficwPcZgMaxColor', 'measurement.asmhiZaMaxColor', 'measurement.asmhiZbMaxColor', 'measurement.asmhiZcMaxColor', 'measurement.asmhiZdMaxColor', 'measurement.bcmiZaMaxColor', 'measurement.bcmiZbMaxColor', 'measurement.bcmiZcMaxColor', 'measurement.bcmiZdMaxColor', 'measurement.imcZaMaxColor', 'measurement.imcZbMaxColor', 'measurement.imcZcMaxColor', 'measurement.imcZdMaxColor', 'measurement.imcZeMaxColor', 'measurement.imcZfMaxColor', 'measurement.imcZgMaxColor', 'measurement.fmslmirZaMaxColor', 'measurement.fmslmirZbMaxColor', 'measurement.fmirZaMaxColor', 'measurement.fmirZbMaxColor', 'measurement.slmirZaMaxColor', 'measurement.slmirZbMaxColor', 'measurement.whrZaMaxColor', 'measurement.whrZbMaxColor', 'measurement.whtrZaMaxColor', 'measurement.whtrZbMaxColor', 'measurement.totalCcScZaMaxColor', 'measurement.totalCcScZbMaxColor', 'measurement.totalCcScZcMaxColor', 'measurement.totalMuhScZaMaxColor', 'measurement.totalMuhScZbMaxColor', 'measurement.totalMuhScZcMaxColor', 'measurement.cibleZaColor', 'measurement.cibleZbColor', 'measurement.cibleZcColor', 'measurement.cibleZdColor', 'measurement.cibleZeColor', 'measurement.cibleZfColor', 'measurement.asmliColor', 'measurement.asmtliColor', 'measurement.request', 'measurement.response', 'measurement.biodyBluetoothMacAddress', 'measurement.machineBluetoothMacAddress'];

    protected $countBy = 'id';

    public function alias()
    {
        return 'measurement';
    }

    public function initRepository()
    {
        $this->pushJoin(MeasurementJoin::class);
    }
}
