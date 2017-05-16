<?php

namespace ContinuousNet\LivnYouBundle\Services;

use ContinuousNet\LivnYouBundle\Entity\Measurement;
use Doctrine\ORM\EntityManager;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * MeasurementInterpretation
 *
 * @author Sahbi KHALFALLAH <sahbi.khalfallah@continuousnet.com>
 */
class MeasurementInterpretation
{
    protected $entityManger;
    protected $translator;
    protected $logger;
    protected $parameters;

    public function __construct(EntityManager $entityManager, TranslatorInterface $translator, Logger $logger, $parameters)
    {
        $this->entityManger = $entityManager;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->parameters = $parameters;
    }

    private function getParameter($parameterKey)
    {
        if (array_key_exists($parameterKey, $this->parameters)) {
            return $this->parameters[$parameterKey];
        }

        throw new \Exception(sprintf(
                'No parameter exist with this key: %s', $parameterKey)
        );
    }

    private function postData($url, $fields)
    {
        $fieldsString = http_build_query($fields);

        $channel = curl_init();

        curl_setopt($channel, CURLOPT_URL, $url);
        curl_setopt($channel, CURLOPT_POST, count($fields));
        curl_setopt($channel, CURLOPT_POSTFIELDS, $fieldsString);
        curl_setopt($channel, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($channel);

        curl_close($channel);

        return $response;
    }

    public function getToken()
    {
        $url = $this->getParameter('base_url') . $this->getParameter('connection_action');

        $fields = array(
            'key' => urlencode($this->getParameter('auth_key')),
        );

        $response = $this->postData($url, $fields);

        $data = json_decode($response);
      
        if (is_null($data)) {
            throw new \Exception(sprintf(
                    'No response for MeasurementInterpretation::getToken - url : %s', $url)
            );
        } else {
            if (isset($data->token)) {
                return $data->token;
            } else {
                throw new \Exception(sprintf(
                        'No token in MeasurementInterpretation::getToken - response : %s', $response)
                );
            }
        }
    }

    public function getMeasurementInterpretation(Measurement $measurement)
    {
        try {
        } catch (\Exception $ex) {
        }

        $token = $this->getToken();

        $url = $this->getParameter('base_url') . $this->getParameter('measurement_action');

        if ($measurement->getGender() == 'Male') {
            $sex = $this->getParameter('male_value');
        } elseif ($measurement->getGender() == 'Female') {
            $sex = $this->getParameter('female_value');
        }

        $birthday = $measurement->getBirthDate()->format($this->getParameter('birth_date_format'));
        $physicalAct = null;
        if (!is_null($measurement->getPhysicalActivity())) {
            $physicalAct = $measurement->getPhysicalActivity()->getId();
        }
        $cupSize = 1;
        $alpha = range('A', 'F');
        $cupSizeAlpha = $measurement->getCupSize();
        if (!is_null($cupSizeAlpha) && $cupSizeAlpha != 'N/A') {
            $cupSize = array_search($cupSizeAlpha, $alpha);
        }

        $fields = array(
            'token' => $token,
            'weight' => $measurement->getWeight(),
            'height' => $measurement->getHeight(),
            'sex' => $sex,
            'birthday' => $birthday,
            'physical_act' => $physicalAct,
            'z200' => $measurement->getZ200(),
            'z50' => $measurement->getZ50(),
            'z5' => $measurement->getZ5(),
            'a50' => $measurement->getA50(),
            'cupsize' => $cupSize,
            'wac' => $measurement->getWaistSize(),
            'hac' => $measurement->getHipsSize(),
            'date' => $measurement->getCreatedAt()->format('Y-m-d'),
            'time' => $measurement->getCreatedAt()->format('H:i:s')
        );

        $measurement->setRequest(json_encode($fields));

        $response = $this->postData($url, $fields);

        $measurement->setResponse(json_encode($response));

        $this->entityManager->flush();

        $data = json_decode($response);

        if (is_null($data)) {
            throw new \Exception(sprintf(
                    'No response for MeasurementInterpretation::getMeasurementInterpretation - url : %s', $url)
            );
        } else {
            if (isset($data->bmr)) {
                return $data;
            } else {
                throw new \Exception(sprintf(
                        'No valid data in MeasurementInterpretation::getMeasurementInterpretation - response : %s', $response)
                );
            }
        }
    }

    public function updateMeasurementInterpretation($measurementId)
    {
        $measurement = $this->entityManager->getRepository('LivnYouBundle:Measurement')->find($measurementId);
        if (!is_null($measurement)) {
            if (!is_null($measurement)) {
                $interpretation = $this->getMeasurementInterpretation($measurement);

                try {
                    $this->mapValues($measurement, $interpretation);
                    $measurement->setStatus('Analyzed');
                    $measurement->setInterpretationDate(new \DateTime());
                    $measurement->setModifierUser($measurement->getCreatorUser());
                    $this->entityManager->flush();
                } catch (\Exception $ex) {
                    throw $ex;
                }
            } else {
                throw new \Exception(sprintf(
                        'No Patient MeasurementInterpretation::updateMeasurementInterpretation Measurement : %s', $measurementId)
                );
            }
        } else {
            throw new \Exception(sprintf(
                'No Measurement ID MeasurementInterpretation::updateMeasurementInterpretation Measurement : %s', $measurementId)
            );
        }
    }

    public function mapValues(Measurement $measurement, $interpretation)
    {
        if (isset($interpretation->age)) {
            $measurement->setAge($interpretation->age);
        }
        if (isset($interpretation->act)) {
            $measurement->setAct($interpretation->act);
        }
        if (isset($interpretation->k)) {
            $measurement->setK($interpretation->k);
        }
        if (isset($interpretation->ecw_pc_ref)) {
            $measurement->setEcwPcRef($interpretation->ecw_pc_ref);
        }
        if (isset($interpretation->icw_pc_ref)) {
            $measurement->setIcwPcRef($interpretation->icw_pc_ref);
        }
        if (isset($interpretation->smi_ref)) {
            $measurement->setSmiRef($interpretation->smi_ref);
        }
        if (isset($interpretation->fmir_cc_ref)) {
            $measurement->setFmirCcRef($interpretation->fmir_cc_ref);
        }
        if (isset($interpretation->fmir_muh_ref)) {
            $measurement->setFmirMuhRef($interpretation->fmir_muh_ref);
        }
        if (isset($interpretation->fmslmir_muh_ref)) {
            $measurement->setFmslmirMuhRef($interpretation->fmslmir_muh_ref);
        }
        if (isset($interpretation->fmslmir_cc_ref)) {
            $measurement->setFmslmirCcRef($interpretation->fmslmir_cc_ref);
        }
        if (isset($interpretation->slmir_muh_ref)) {
            $measurement->setSlmirMuhRef($interpretation->slmir_muh_ref);
        }
        if (isset($interpretation->slmir_cc_ref)) {
            $measurement->setSlmirCcRef($interpretation->slmir_cc_ref);
        }
        if (isset($interpretation->whr_ref)) {
            $measurement->setWhrRef($interpretation->whr_ref);
        }
        if (isset($interpretation->hac)) {
            $measurement->setHac($interpretation->hac);
        }
        if (isset($interpretation->wac)) {
            $measurement->setWac($interpretation->wac);
        }
        if (isset($interpretation->a50_radian)) {
            $measurement->setA50Radian($interpretation->a50_radian);
        }
        if (isset($interpretation->x50)) {
            $measurement->setX50($interpretation->x50);
        }
        if (isset($interpretation->r50)) {
            $measurement->setR50($interpretation->r50);
        }
        if (isset($interpretation->bmr_ref)) {
            $measurement->setBmrRef($interpretation->bmr_ref);
        }
        if (isset($interpretation->imc)) {
            $measurement->setImc($interpretation->imc);
        }
        if (isset($interpretation->imc_ref)) {
            $measurement->setImcRef($interpretation->imc_ref);
        }
        if (isset($interpretation->imc_ref_inf)) {
            $measurement->setImcRefInf($interpretation->imc_ref_inf);
        }
        if (isset($interpretation->imc_ref_sup)) {
            $measurement->setImcRefSup($interpretation->imc_ref_sup);
        }
        if (isset($interpretation->fm_pc_ref)) {
            $measurement->setFmPcRef($interpretation->fm_pc_ref);
        }
        if (isset($interpretation->tbw)) {
            $measurement->setTbw($interpretation->tbw);
        }
        if (isset($interpretation->ecw)) {
            $measurement->setEcw($interpretation->ecw);
        }
        if (isset($interpretation->bmci)) {
            $measurement->setBmci($interpretation->bmci);
        }
        if (isset($interpretation->fm_hc_ref_kg)) {
            $measurement->setFmHcRefKg($interpretation->fm_hc_ref_kg);
        }
        if (isset($interpretation->fm_ref_kg)) {
            $measurement->setFmRefKg($interpretation->fm_ref_kg);
        }
        if (isset($interpretation->ffm_kg)) {
            $measurement->setFfmKg($interpretation->ffm_kg);
        }
        if (isset($interpretation->ffmi_ref)) {
            $measurement->setFfmiRef($interpretation->ffmi_ref);
        }
        if (isset($interpretation->ffm_ref_kg)) {
            $measurement->setFfmRefKg($interpretation->ffm_ref_kg);
        }
        if (isset($interpretation->fm_kg)) {
            $measurement->setFmKg($interpretation->fm_kg);
        }
        if (isset($interpretation->ffm_pc)) {
            $measurement->setFfmPc($interpretation->ffm_pc);
        }
        if (isset($interpretation->ffm_et_kg)) {
            $measurement->setFfmEtKg($interpretation->ffm_et_kg);
        }
        if (isset($interpretation->idffm_kg)) {
            $measurement->setIdffmKg($interpretation->idffm_kg);
        }
        if (isset($interpretation->dffm_kg)) {
            $measurement->setDffmKg($interpretation->dffm_kg);
        }
        if (isset($interpretation->dffm_ref_kg)) {
            $measurement->setDffmRefKg($interpretation->dffm_ref_kg);
        }
        if (isset($interpretation->dffm_et_kg)) {
            $measurement->setDffmEtKg($interpretation->dffm_et_kg);
        }
        if (isset($interpretation->asmm_kg)) {
            $measurement->setAsmmKg($interpretation->asmm_kg);
        }
        if (isset($interpretation->asmm_ref)) {
            $measurement->setAsmmRef($interpretation->asmm_ref);
        }
        if (isset($interpretation->asmm_et)) {
            $measurement->setAsmmEt($interpretation->asmm_et);
        }
        if (isset($interpretation->asmmffmr)) {
            $measurement->setAsmmffmr($interpretation->asmmffmr);
        }
        if (isset($interpretation->tbw_pc)) {
            $measurement->setTbwPc($interpretation->tbw_pc);
        }
        if (isset($interpretation->tbwffm_pc)) {
            $measurement->setTbwffmPc($interpretation->tbwffm_pc);
        }
        if (isset($interpretation->tbwffm_pc_ref)) {
            $measurement->setTbwffmPcRef($interpretation->tbwffm_pc_ref);
        }
        if (isset($interpretation->tbwffm_pc_et)) {
            $measurement->setTbwffmPcEt($interpretation->tbwffm_pc_et);
        }
        if (isset($interpretation->tbwffm_hc_kg)) {
            $measurement->setTbwffmHcKg($interpretation->tbwffm_hc_kg);
        }
        if (isset($interpretation->tbwffm_kg_ref)) {
            $measurement->setTbwffmKgRef($interpretation->tbwffm_kg_ref);
        }
        if (isset($interpretation->ffw_pc)) {
            $measurement->setFfwPc($interpretation->ffw_pc);
        }
        if (isset($interpretation->ffw)) {
            $measurement->setFfw($interpretation->ffw);
        }
        if (isset($interpretation->ffw_pc_ref)) {
            $measurement->setFfwPcRef($interpretation->ffw_pc_ref);
        }
        if (isset($interpretation->ffw_ref)) {
            $measurement->setFfwRef($interpretation->ffw_ref);
        }
        if (isset($interpretation->ffw_et)) {
            $measurement->setFfwEt($interpretation->ffw_et);
        }
        if (isset($interpretation->ecw_spec)) {
            $measurement->setEcwSpec($interpretation->ecw_spec);
        }
        if (isset($interpretation->ecw_pc)) {
            $measurement->setEcwPc($interpretation->ecw_pc);
        }
        if (isset($interpretation->ecwffm_pc)) {
            $measurement->setEcwffmPc($interpretation->ecwffm_pc);
        }
        if (isset($interpretation->icw)) {
            $measurement->setIcw($interpretation->icw);
        }
        if (isset($interpretation->icw_pc)) {
            $measurement->setIcwPc($interpretation->icw_pc);
        }
        if (isset($interpretation->fficw_pc_ref)) {
            $measurement->setFficwPcRef($interpretation->fficw_pc_ref);
        }
        if (isset($interpretation->fficw_ref)) {
            $measurement->setFficwRef($interpretation->fficw_ref);
        }
        if (isset($interpretation->ffecw_ref)) {
            $measurement->setFfecwRef($interpretation->ffecw_ref);
        }
        if (isset($interpretation->ffecw_pc_ref)) {
            $measurement->setFfecwPcRef($interpretation->ffecw_pc_ref);
        }
        if (isset($interpretation->ecwicw_pc_et)) {
            $measurement->setEcwicwPcEt($interpretation->ecwicw_pc_et);
        }
        if (isset($interpretation->cmo)) {
            $measurement->setCmo($interpretation->cmo);
        }
        if (isset($interpretation->slm)) {
            $measurement->setSlm($interpretation->slm);
        }
        if (isset($interpretation->mo)) {
            $measurement->setMo($interpretation->mo);
        }
        if (isset($interpretation->ecs)) {
            $measurement->setEcs($interpretation->ecs);
        }
        if (isset($interpretation->ms)) {
            $measurement->setMs($interpretation->ms);
        }
        if (isset($interpretation->ms_ref)) {
            $measurement->setMsRef($interpretation->ms_ref);
        }
        if (isset($interpretation->smi)) {
            $measurement->setSmi($interpretation->smi);
        }
        if (isset($interpretation->fmi_indice_comp)) {
            $measurement->setFmiIndiceComp($interpretation->fmi_indice_comp);
        }
        if (isset($interpretation->slmir)) {
            $measurement->setSlmir($interpretation->slmir);
        }
        if (isset($interpretation->dasmm_kg)) {
            $measurement->setDasmmKg($interpretation->dasmm_kg);
        }
        if (isset($interpretation->ffeir_ref)) {
            $measurement->setFfeirRef($interpretation->ffeir_ref);
        }
        if (isset($interpretation->bmr_ref_kjoules)) {
            $measurement->setBmrRefKjoules($interpretation->bmr_ref_kjoules);
        }
        if (isset($interpretation->mms_pc_ref)) {
            $measurement->setMmsPcRef($interpretation->mms_pc_ref);
        }
        if (isset($interpretation->tbw_fm)) {
            $measurement->setTbwFm($interpretation->tbw_fm);
        }
        if (isset($interpretation->z200z5r)) {
            $measurement->setZ200z5r($interpretation->z200z5r);
        }
        if (isset($interpretation->whr)) {
            if (is_numeric($interpretation->whr)) {
                $measurement->setWhr($interpretation->whr);
            }
        }
        if (isset($interpretation->whtr)) {
            $measurement->setWhtr($interpretation->whtr);
        }
        if (isset($interpretation->whtr_ref)) {
            $measurement->setWhtrRef($interpretation->whtr_ref);
        }
        if (isset($interpretation->bmr)) {
            $measurement->setBmr($interpretation->bmr);
        }
        if (isset($interpretation->bmr_kjoules)) {
            $measurement->setBmrKjoules($interpretation->bmr_kjoules);
        }
        if (isset($interpretation->adcr)) {
            $measurement->setAdcr($interpretation->adcr);
        }
        if (isset($interpretation->adcr_kjoules)) {
            $measurement->setAdcrKjoules($interpretation->adcr_kjoules);
        }
        if (isset($interpretation->fm_hc_pc)) {
            $measurement->setFmHcPc($interpretation->fm_hc_pc);
        }
        if (isset($interpretation->fm_hc_kg)) {
            $measurement->setFmHcKg($interpretation->fm_hc_kg);
        }
        if (isset($interpretation->fm_hc_pc_ref)) {
            $measurement->setFmHcPcRef($interpretation->fm_hc_pc_ref);
        }
        if (isset($interpretation->fm_hc_et_kg)) {
            $measurement->setFmHcEtKg($interpretation->fm_hc_et_kg);
        }
        if (isset($interpretation->fm_et_kg)) {
            $measurement->setFmEtKg($interpretation->fm_et_kg);
        }
        if (isset($interpretation->fm_pc)) {
            $measurement->setFmPc($interpretation->fm_pc);
        }
        if (isset($interpretation->mms_kg)) {
            $measurement->setMmsKg($interpretation->mms_kg);
        }
        if (isset($interpretation->mms_pc)) {
            $measurement->setMmsPc($interpretation->mms_pc);
        }
        if (isset($interpretation->mms_ref_kg)) {
            $measurement->setMmsRefKg($interpretation->mms_ref_kg);
        }
        if (isset($interpretation->mms_et_kg)) {
            $measurement->setMmsEtKg($interpretation->mms_et_kg);
        }
        if (isset($interpretation->bcm)) {
            $measurement->setBcm($interpretation->bcm);
        }
        if (isset($interpretation->mp_meta_kg)) {
            $measurement->setMpMetaKg($interpretation->mp_meta_kg);
        }
        if (isset($interpretation->mp_meta_pc)) {
            $measurement->setMpMetaPc($interpretation->mp_meta_pc);
        }
        if (isset($interpretation->tbw_ref)) {
            $measurement->setTbwRef($interpretation->tbw_ref);
        }
        if (isset($interpretation->icwfm)) {
            $measurement->setIcwfm($interpretation->icwfm);
        }
        if (isset($interpretation->ecwfm)) {
            $measurement->setEcwfm($interpretation->ecwfm);
        }
        if (isset($interpretation->fficw)) {
            $measurement->setFficw($interpretation->fficw);
        }
        if (isset($interpretation->ffecw)) {
            $measurement->setFfecw($interpretation->ffecw);
        }
        if (isset($interpretation->ffecw_pc)) {
            $measurement->setFfecwPc($interpretation->ffecw_pc);
        }
        if (isset($interpretation->fficw_pc)) {
            $measurement->setFficwPc($interpretation->fficw_pc);
        }
        if (isset($interpretation->fficw_et)) {
            $measurement->setFficwEt($interpretation->fficw_et);
        }
        if (isset($interpretation->ffecw_et)) {
            $measurement->setFfecwEt($interpretation->ffecw_et);
        }
        if (isset($interpretation->ffecwicw_pc_et)) {
            $measurement->setFfecwicwPcEt($interpretation->ffecwicw_pc_et);
        }
        if (isset($interpretation->ffeir)) {
            $measurement->setFfeir($interpretation->ffeir);
        }
        if (isset($interpretation->mp)) {
            $measurement->setMp($interpretation->mp);
        }
        if (isset($interpretation->mmhi)) {
            $measurement->setMmhi($interpretation->mmhi);
        }
        if (isset($interpretation->asmhi)) {
            $measurement->setAsmhi($interpretation->asmhi);
        }
        if (isset($interpretation->asmli)) {
            $measurement->setAsmli($interpretation->asmli);
        }
        if (isset($interpretation->bcmffmr)) {
            $measurement->setBcmffmr($interpretation->bcmffmr);
        }
        if (isset($interpretation->fmir)) {
            $measurement->setFmir($interpretation->fmir);
        }
        if (isset($interpretation->fmslmir)) {
            $measurement->setFmslmir($interpretation->fmslmir);
        }
        if (isset($interpretation->fm_hc_pc_et)) {
            $measurement->setFmHcPcEt($interpretation->fm_hc_pc_et);
        }
        if (isset($interpretation->fm_pc_et)) {
            $measurement->setFmPcEt($interpretation->fm_pc_et);
        }
        if (isset($interpretation->fmi)) {
            $measurement->setFmi($interpretation->fmi);
        }
        if (isset($interpretation->ffecw_ref_div_ffecw)) {
            $measurement->setFfecwRefDivFfecw($interpretation->ffecw_ref_div_ffecw);
        }
        if (isset($interpretation->fficw_ref_div_fficw)) {
            $measurement->setFficwRefDivFficw($interpretation->fficw_ref_div_fficw);
        }
        if (isset($interpretation->icw_et)) {
            $measurement->setIcwEt($interpretation->icw_et);
        }
        if (isset($interpretation->icw_ref)) {
            $measurement->setIcwRef($interpretation->icw_ref);
        }
        if (isset($interpretation->icwi)) {
            $measurement->setIcwi($interpretation->icwi);
        }
        if (isset($interpretation->bmr_et)) {
            $measurement->setBmrEt($interpretation->bmr_et);
        }
        if (isset($interpretation->mms_pc_et)) {
            $measurement->setMmsPcEt($interpretation->mms_pc_et);
        }
        if (isset($interpretation->tbe)) {
            $measurement->setTbe($interpretation->tbe);
        }
        if (isset($interpretation->cmo_ref)) {
            $measurement->setCmoRef($interpretation->cmo_ref);
        }
        if (isset($interpretation->cmo_et)) {
            $measurement->setCmoEt($interpretation->cmo_et);
        }
        if (isset($interpretation->slm_ref)) {
            $measurement->setSlmRef($interpretation->slm_ref);
        }
        if (isset($interpretation->slm_et)) {
            $measurement->setSlmEt($interpretation->slm_et);
        }
        if (isset($interpretation->asmtli)) {
            $measurement->setAsmtli($interpretation->asmtli);
        }
        if (isset($interpretation->ecs_ref)) {
            $measurement->setEcsRef($interpretation->ecs_ref);
        }
        if (isset($interpretation->ecs_et)) {
            $measurement->setEcsEt($interpretation->ecs_et);
        }
        if (isset($interpretation->mp_meta_ref)) {
            $measurement->setMpMetaRef($interpretation->mp_meta_ref);
        }
        if (isset($interpretation->bcm_ref)) {
            $measurement->setBcmRef($interpretation->bcm_ref);
        }
        if (isset($interpretation->bcm_et)) {
            $measurement->setBcmEt($interpretation->bcm_et);
        }
        if (isset($interpretation->mp_ref)) {
            $measurement->setMpRef($interpretation->mp_ref);
        }
        if (isset($interpretation->mp_et)) {
            $measurement->setMpEt($interpretation->mp_et);
        }
        if (isset($interpretation->mp_meta_et_kg)) {
            $measurement->setMpMetaEtKg($interpretation->mp_meta_et_kg);
        }
        if (isset($interpretation->fm_hc_pc_100)) {
            $measurement->setFmHcPc100($interpretation->fm_hc_pc_100);
        }
        if (isset($interpretation->fm_hc_pc_std_a)) {
            $measurement->setFmHcPcStdA($interpretation->fm_hc_pc_std_a);
        }
        if (isset($interpretation->fm_hc_pc_std_b)) {
            $measurement->setFmHcPcStdB($interpretation->fm_hc_pc_std_b);
        }
        if (isset($interpretation->fm_hc_pc_std_c)) {
            $measurement->setFmHcPcStdC($interpretation->fm_hc_pc_std_c);
        }
        if (isset($interpretation->fm_hc_pc_std_d)) {
            $measurement->setFmHcPcStdD($interpretation->fm_hc_pc_std_d);
        }
        if (isset($interpretation->fm_hc_pc_std_e)) {
            $measurement->setFmHcPcStdE($interpretation->fm_hc_pc_std_e);
        }
        if (isset($interpretation->fm_hc_pc_std_f)) {
            $measurement->setFmHcPcStdF($interpretation->fm_hc_pc_std_f);
        }
        if (isset($interpretation->fm_hc_pc_za_max)) {
            $measurement->setFmHcPcZaMax($interpretation->fm_hc_pc_za_max);
        }
        if (isset($interpretation->fm_hc_pc_za_max_color)) {
            $measurement->setFmHcPcZaMaxColor($interpretation->fm_hc_pc_za_max_color);
        }
        if (isset($interpretation->fm_hc_pc_zb_max)) {
            $measurement->setFmHcPcZbMax($interpretation->fm_hc_pc_zb_max);
        }
        if (isset($interpretation->fm_hc_pc_zb_max_color)) {
            $measurement->setFmHcPcZbMaxColor($interpretation->fm_hc_pc_zb_max_color);
        }
        if (isset($interpretation->fm_hc_pc_zc_max)) {
            $measurement->setFmHcPcZcMax($interpretation->fm_hc_pc_zc_max);
        }
        if (isset($interpretation->fm_hc_pc_zc_max_color)) {
            $measurement->setFmHcPcZcMaxColor($interpretation->fm_hc_pc_zc_max_color);
        }
        if (isset($interpretation->fm_hc_pc_zd_max)) {
            $measurement->setFmHcPcZdMax($interpretation->fm_hc_pc_zd_max);
        }
        if (isset($interpretation->fm_hc_pc_zd_max_color)) {
            $measurement->setFmHcPcZdMaxColor($interpretation->fm_hc_pc_zd_max_color);
        }
        if (isset($interpretation->fm_hc_pc_ze_max)) {
            $measurement->setFmHcPcZeMax($interpretation->fm_hc_pc_ze_max);
        }
        if (isset($interpretation->fm_hc_pc_ze_max_color)) {
            $measurement->setFmHcPcZeMaxColor($interpretation->fm_hc_pc_ze_max_color);
        }
        if (isset($interpretation->fm_hc_pc_zf_max)) {
            $measurement->setFmHcPcZfMax($interpretation->fm_hc_pc_zf_max);
        }
        if (isset($interpretation->fm_hc_pc_zf_max_color)) {
            $measurement->setFmHcPcZfMaxColor($interpretation->fm_hc_pc_zf_max_color);
        }
        if (isset($interpretation->fm_hc_pc_zone)) {
            $measurement->setFmHcPcZone($interpretation->fm_hc_pc_zone);
        }
        if (isset($interpretation->ffw_pc_100)) {
            $measurement->setFfwPc100($interpretation->ffw_pc_100);
        }
        if (isset($interpretation->ffw_pc_std_a)) {
            $measurement->setFfwPcStdA($interpretation->ffw_pc_std_a);
        }
        if (isset($interpretation->ffw_pc_std_b)) {
            $measurement->setFfwPcStdB($interpretation->ffw_pc_std_b);
        }
        if (isset($interpretation->ffw_pc_std_c)) {
            $measurement->setFfwPcStdC($interpretation->ffw_pc_std_c);
        }
        if (isset($interpretation->ffw_pc_std_d)) {
            $measurement->setFfwPcStdD($interpretation->ffw_pc_std_d);
        }
        if (isset($interpretation->ffw_pc_std_e)) {
            $measurement->setFfwPcStdE($interpretation->ffw_pc_std_e);
        }
        if (isset($interpretation->ffw_pc_std_f)) {
            $measurement->setFfwPcStdF($interpretation->ffw_pc_std_f);
        }
        if (isset($interpretation->ffw_pc_std_g)) {
            $measurement->setFfwPcStdG($interpretation->ffw_pc_std_g);
        }
        if (isset($interpretation->ffw_pc_za_max)) {
            $measurement->setFfwPcZaMax($interpretation->ffw_pc_za_max);
        }
        if (isset($interpretation->ffw_pc_za_max_color)) {
            $measurement->setFfwPcZaMaxColor($interpretation->ffw_pc_za_max_color);
        }
        if (isset($interpretation->ffw_pc_zb_max)) {
            $measurement->setFfwPcZbMax($interpretation->ffw_pc_zb_max);
        }
        if (isset($interpretation->ffw_pc_zb_max_color)) {
            $measurement->setFfwPcZbMaxColor($interpretation->ffw_pc_zb_max_color);
        }
        if (isset($interpretation->ffw_pc_zc_max)) {
            $measurement->setFfwPcZcMax($interpretation->ffw_pc_zc_max);
        }
        if (isset($interpretation->ffw_pc_zc_max_color)) {
            $measurement->setFfwPcZcMaxColor($interpretation->ffw_pc_zc_max_color);
        }
        if (isset($interpretation->ffw_pc_zd_max)) {
            $measurement->setFfwPcZdMax($interpretation->ffw_pc_zd_max);
        }
        if (isset($interpretation->ffw_pc_zd_max_color)) {
            $measurement->setFfwPcZdMaxColor($interpretation->ffw_pc_zd_max_color);
        }
        if (isset($interpretation->ffw_pc_ze_max)) {
            $measurement->setFfwPcZeMax($interpretation->ffw_pc_ze_max);
        }
        if (isset($interpretation->ffw_pc_ze_max_color)) {
            $measurement->setFfwPcZeMaxColor($interpretation->ffw_pc_ze_max_color);
        }
        if (isset($interpretation->ffw_pc_zf_max)) {
            $measurement->setFfwPcZfMax($interpretation->ffw_pc_zf_max);
        }
        if (isset($interpretation->ffw_pc_zf_max_color)) {
            $measurement->setFfwPcZfMaxColor($interpretation->ffw_pc_zf_max_color);
        }
        if (isset($interpretation->ffw_pc_zg_max)) {
            $measurement->setFfwPcZgMax($interpretation->ffw_pc_zg_max);
        }
        if (isset($interpretation->ffw_pc_zg_max_color)) {
            $measurement->setFfwPcZgMaxColor($interpretation->ffw_pc_zg_max_color);
        }
        if (isset($interpretation->ffw_pc_zone)) {
            $measurement->setFfwPcZone($interpretation->ffw_pc_zone);
        }
        if (isset($interpretation->mmhi_std_a)) {
            $measurement->setMmhiStdA($interpretation->mmhi_std_a);
        }
        if (isset($interpretation->mmhi_std_b)) {
            $measurement->setMmhiStdB($interpretation->mmhi_std_b);
        }
        if (isset($interpretation->mmhi_std_c)) {
            $measurement->setMmhiStdC($interpretation->mmhi_std_c);
        }
        if (isset($interpretation->mmhi_std_d)) {
            $measurement->setMmhiStdD($interpretation->mmhi_std_d);
        }
        if (isset($interpretation->mmhi_za_max)) {
            $measurement->setMmhiZaMax($interpretation->mmhi_za_max);
        }
        if (isset($interpretation->mmhi_za_max_color)) {
            $measurement->setMmhiZaMaxColor($interpretation->mmhi_za_max_color);
        }
        if (isset($interpretation->mmhi_zb_max)) {
            $measurement->setMmhiZbMax($interpretation->mmhi_zb_max);
        }
        if (isset($interpretation->mmhi_zb_max_color)) {
            $measurement->setMmhiZbMaxColor($interpretation->mmhi_zb_max_color);
        }
        if (isset($interpretation->mmhi_zc_max)) {
            $measurement->setMmhiZcMax($interpretation->mmhi_zc_max);
        }
        if (isset($interpretation->mmhi_zc_max_color)) {
            $measurement->setMmhiZcMaxColor($interpretation->mmhi_zc_max_color);
        }
        if (isset($interpretation->mmhi_zd_max)) {
            $measurement->setMmhiZdMax($interpretation->mmhi_zd_max);
        }
        if (isset($interpretation->mmhi_zd_max_color)) {
            $measurement->setMmhiZdMaxColor($interpretation->mmhi_zd_max_color);
        }
        if (isset($interpretation->mmhi_zone)) {
            $measurement->setMmhiZone($interpretation->mmhi_zone);
        }
        if (isset($interpretation->fm_hc_pc_inf)) {
            $measurement->setFmHcPcInf($interpretation->fm_hc_pc_inf);
        }
        if (isset($interpretation->adcr_za_max)) {
            $measurement->setAdcrZaMax($interpretation->adcr_za_max);
        }
        if (isset($interpretation->adcr_za_max_color)) {
            $measurement->setAdcrZaMaxColor($interpretation->adcr_za_max_color);
        }
        if (isset($interpretation->adcr_zb_max)) {
            $measurement->setAdcrZbMax($interpretation->adcr_zb_max);
        }
        if (isset($interpretation->adcr_zb_max_color)) {
            $measurement->setAdcrZbMaxColor($interpretation->adcr_zb_max_color);
        }
        if (isset($interpretation->adcr_zc_max)) {
            $measurement->setAdcrZcMax($interpretation->adcr_zc_max);
        }
        if (isset($interpretation->adcr_zc_max_color)) {
            $measurement->setAdcrZcMaxColor($interpretation->adcr_zc_max_color);
        }
        if (isset($interpretation->adcr_zd_max)) {
            $measurement->setAdcrZdMax($interpretation->adcr_zd_max);
        }
        if (isset($interpretation->adcr_zd_max_color)) {
            $measurement->setAdcrZdMaxColor($interpretation->adcr_zd_max_color);
        }
        if (isset($interpretation->adcr_ze_max)) {
            $measurement->setAdcrZeMax($interpretation->adcr_ze_max);
        }
        if (isset($interpretation->adcr_ze_max_color)) {
            $measurement->setAdcrZeMaxColor($interpretation->adcr_ze_max_color);
        }
        if (isset($interpretation->adcr_zone)) {
            $measurement->setAdcrZone($interpretation->adcr_zone);
        }
        if (isset($interpretation->asmmi)) {
            $measurement->setAsmmi($interpretation->asmmi);
        }
        if (isset($interpretation->asmmi_std_a)) {
            $measurement->setAsmmiStdA($interpretation->asmmi_std_a);
        }
        if (isset($interpretation->asmmi_std_b)) {
            $measurement->setAsmmiStdB($interpretation->asmmi_std_b);
        }
        if (isset($interpretation->asmmi_std_c)) {
            $measurement->setAsmmiStdC($interpretation->asmmi_std_c);
        }
        if (isset($interpretation->asmmi_std_d)) {
            $measurement->setAsmmiStdD($interpretation->asmmi_std_d);
        }
        if (isset($interpretation->asmmi_za_max)) {
            $measurement->setAsmmiZaMax($interpretation->asmmi_za_max);
        }
        if (isset($interpretation->asmmi_za_max_color)) {
            $measurement->setAsmmiZaMaxColor($interpretation->asmmi_za_max_color);
        }
        if (isset($interpretation->asmmi_zb_max)) {
            $measurement->setAsmmiZbMax($interpretation->asmmi_zb_max);
        }
        if (isset($interpretation->asmmi_zb_max_color)) {
            $measurement->setAsmmiZbMaxColor($interpretation->asmmi_zb_max_color);
        }
        if (isset($interpretation->asmmi_zc_max)) {
            $measurement->setAsmmiZcMax($interpretation->asmmi_zc_max);
        }
        if (isset($interpretation->asmmi_zc_max_color)) {
            $measurement->setAsmmiZcMaxColor($interpretation->asmmi_zc_max_color);
        }
        if (isset($interpretation->asmmi_zd_max)) {
            $measurement->setAsmmiZdMax($interpretation->asmmi_zd_max);
        }
        if (isset($interpretation->asmmi_zd_max_color)) {
            $measurement->setAsmmiZdMaxColor($interpretation->asmmi_zd_max_color);
        }
        if (isset($interpretation->asmmi_zone)) {
            $measurement->setAsmmiZone($interpretation->asmmi_zone);
        }
        if (isset($interpretation->ecw_pc_100)) {
            $measurement->setEcwPc100($interpretation->ecw_pc_100);
        }
        if (isset($interpretation->ecw_pc_ref_100)) {
            $measurement->setEcwPcRef100($interpretation->ecw_pc_ref_100);
        }
        if (isset($interpretation->ecw_pc_std_a)) {
            $measurement->setEcwPcStdA($interpretation->ecw_pc_std_a);
        }
        if (isset($interpretation->ecw_pc_std_b)) {
            $measurement->setEcwPcStdB($interpretation->ecw_pc_std_b);
        }
        if (isset($interpretation->ecw_pc_std_c)) {
            $measurement->setEcwPcStdC($interpretation->ecw_pc_std_c);
        }
        if (isset($interpretation->ecw_pc_std_d)) {
            $measurement->setEcwPcStdD($interpretation->ecw_pc_std_d);
        }
        if (isset($interpretation->ecw_pc_std_e)) {
            $measurement->setEcwPcStdE($interpretation->ecw_pc_std_e);
        }
        if (isset($interpretation->ecw_pc_std_f)) {
            $measurement->setEcwPcStdF($interpretation->ecw_pc_std_f);
        }
        if (isset($interpretation->ecw_pc_std_g)) {
            $measurement->setEcwPcStdG($interpretation->ecw_pc_std_g);
        }
        if (isset($interpretation->ecw_pc_za_max)) {
            $measurement->setEcwPcZaMax($interpretation->ecw_pc_za_max);
        }
        if (isset($interpretation->ecw_pc_za_max_color)) {
            $measurement->setEcwPcZaMaxColor($interpretation->ecw_pc_za_max_color);
        }
        if (isset($interpretation->ecw_pc_zb_max)) {
            $measurement->setEcwPcZbMax($interpretation->ecw_pc_zb_max);
        }
        if (isset($interpretation->ecw_pc_zb_max_color)) {
            $measurement->setEcwPcZbMaxColor($interpretation->ecw_pc_zb_max_color);
        }
        if (isset($interpretation->ecw_pc_zc_max)) {
            $measurement->setEcwPcZcMax($interpretation->ecw_pc_zc_max);
        }
        if (isset($interpretation->ecw_pc_zc_max_color)) {
            $measurement->setEcwPcZcMaxColor($interpretation->ecw_pc_zc_max_color);
        }
        if (isset($interpretation->ecw_pc_zd_max)) {
            $measurement->setEcwPcZdMax($interpretation->ecw_pc_zd_max);
        }
        if (isset($interpretation->ecw_pc_zd_max_color)) {
            $measurement->setEcwPcZdMaxColor($interpretation->ecw_pc_zd_max_color);
        }
        if (isset($interpretation->ecw_pc_ze_max)) {
            $measurement->setEcwPcZeMax($interpretation->ecw_pc_ze_max);
        }
        if (isset($interpretation->ecw_pc_ze_max_color)) {
            $measurement->setEcwPcZeMaxColor($interpretation->ecw_pc_ze_max_color);
        }
        if (isset($interpretation->ecw_pc_zf_max)) {
            $measurement->setEcwPcZfMax($interpretation->ecw_pc_zf_max);
        }
        if (isset($interpretation->ecw_pc_zf_max_color)) {
            $measurement->setEcwPcZfMaxColor($interpretation->ecw_pc_zf_max_color);
        }
        if (isset($interpretation->ecw_pc_zg_max)) {
            $measurement->setEcwPcZgMax($interpretation->ecw_pc_zg_max);
        }
        if (isset($interpretation->ecw_pc_zg_max_color)) {
            $measurement->setEcwPcZgMaxColor($interpretation->ecw_pc_zg_max_color);
        }
        if (isset($interpretation->ecw_pc_zone)) {
            $measurement->setEcwPcZone($interpretation->ecw_pc_zone);
        }
        if (isset($interpretation->icw_pc_100)) {
            $measurement->setIcwPc100($interpretation->icw_pc_100);
        }
        if (isset($interpretation->icw_pc_ref_100)) {
            $measurement->setIcwPcRef100($interpretation->icw_pc_ref_100);
        }
        if (isset($interpretation->icw_pc_std_a)) {
            $measurement->setIcwPcStdA($interpretation->icw_pc_std_a);
        }
        if (isset($interpretation->icw_pc_std_b)) {
            $measurement->setIcwPcStdB($interpretation->icw_pc_std_b);
        }
        if (isset($interpretation->icw_pc_std_c)) {
            $measurement->setIcwPcStdC($interpretation->icw_pc_std_c);
        }
        if (isset($interpretation->icw_pc_std_d)) {
            $measurement->setIcwPcStdD($interpretation->icw_pc_std_d);
        }
        if (isset($interpretation->icw_pc_std_e)) {
            $measurement->setIcwPcStdE($interpretation->icw_pc_std_e);
        }
        if (isset($interpretation->icw_pc_std_f)) {
            $measurement->setIcwPcStdF($interpretation->icw_pc_std_f);
        }
        if (isset($interpretation->icw_pc_std_g)) {
            $measurement->setIcwPcStdG($interpretation->icw_pc_std_g);
        }
        if (isset($interpretation->icw_pc_za_max)) {
            $measurement->setIcwPcZaMax($interpretation->icw_pc_za_max);
        }
        if (isset($interpretation->icw_pc_za_max_color)) {
            $measurement->setIcwPcZaMaxColor($interpretation->icw_pc_za_max_color);
        }
        if (isset($interpretation->icw_pc_zb_max)) {
            $measurement->setIcwPcZbMax($interpretation->icw_pc_zb_max);
        }
        if (isset($interpretation->icw_pc_zb_max_color)) {
            $measurement->setIcwPcZbMaxColor($interpretation->icw_pc_zb_max_color);
        }
        if (isset($interpretation->icw_pc_zc_max)) {
            $measurement->setIcwPcZcMax($interpretation->icw_pc_zc_max);
        }
        if (isset($interpretation->icw_pc_zc_max_color)) {
            $measurement->setIcwPcZcMaxColor($interpretation->icw_pc_zc_max_color);
        }
        if (isset($interpretation->icw_pc_zd_max)) {
            $measurement->setIcwPcZdMax($interpretation->icw_pc_zd_max);
        }
        if (isset($interpretation->icw_pc_zd_max_color)) {
            $measurement->setIcwPcZdMaxColor($interpretation->icw_pc_zd_max_color);
        }
        if (isset($interpretation->icw_pc_ze_max)) {
            $measurement->setIcwPcZeMax($interpretation->icw_pc_ze_max);
        }
        if (isset($interpretation->icw_pc_ze_max_color)) {
            $measurement->setIcwPcZeMaxColor($interpretation->icw_pc_ze_max_color);
        }
        if (isset($interpretation->icw_pc_zf_max)) {
            $measurement->setIcwPcZfMax($interpretation->icw_pc_zf_max);
        }
        if (isset($interpretation->icw_pc_zf_max_color)) {
            $measurement->setIcwPcZfMaxColor($interpretation->icw_pc_zf_max_color);
        }
        if (isset($interpretation->icw_pc_zg_max)) {
            $measurement->setIcwPcZgMax($interpretation->icw_pc_zg_max);
        }
        if (isset($interpretation->icw_pc_zg_max_color)) {
            $measurement->setIcwPcZgMaxColor($interpretation->icw_pc_zg_max_color);
        }
        if (isset($interpretation->icw_pc_zone)) {
            $measurement->setIcwPcZone($interpretation->icw_pc_zone);
        }
        if (isset($interpretation->fm_pc_100)) {
            $measurement->setFmPc100($interpretation->fm_pc_100);
        }
        if (isset($interpretation->fm_pc_std_a)) {
            $measurement->setFmPcStdA($interpretation->fm_pc_std_a);
        }
        if (isset($interpretation->fm_pc_std_b)) {
            $measurement->setFmPcStdB($interpretation->fm_pc_std_b);
        }
        if (isset($interpretation->fm_pc_std_c)) {
            $measurement->setFmPcStdC($interpretation->fm_pc_std_c);
        }
        if (isset($interpretation->fm_pc_std_d)) {
            $measurement->setFmPcStdD($interpretation->fm_pc_std_d);
        }
        if (isset($interpretation->fm_pc_std_e)) {
            $measurement->setFmPcStdE($interpretation->fm_pc_std_e);
        }
        if (isset($interpretation->fm_pc_std_f)) {
            $measurement->setFmPcStdF($interpretation->fm_pc_std_f);
        }
        if (isset($interpretation->fm_pc_za_max)) {
            $measurement->setFmPcZaMax($interpretation->fm_pc_za_max);
        }
        if (isset($interpretation->fm_pc_za_max_color)) {
            $measurement->setFmPcZaMaxColor($interpretation->fm_pc_za_max_color);
        }
        if (isset($interpretation->fm_pc_zb_max)) {
            $measurement->setFmPcZbMax($interpretation->fm_pc_zb_max);
        }
        if (isset($interpretation->fm_pc_zb_max_color)) {
            $measurement->setFmPcZbMaxColor($interpretation->fm_pc_zb_max_color);
        }
        if (isset($interpretation->fm_pc_zc_max)) {
            $measurement->setFmPcZcMax($interpretation->fm_pc_zc_max);
        }
        if (isset($interpretation->fm_pc_zc_max_color)) {
            $measurement->setFmPcZcMaxColor($interpretation->fm_pc_zc_max_color);
        }
        if (isset($interpretation->fm_pc_zd_max)) {
            $measurement->setFmPcZdMax($interpretation->fm_pc_zd_max);
        }
        if (isset($interpretation->fm_pc_zd_max_color)) {
            $measurement->setFmPcZdMaxColor($interpretation->fm_pc_zd_max_color);
        }
        if (isset($interpretation->fm_pc_ze_max)) {
            $measurement->setFmPcZeMax($interpretation->fm_pc_ze_max);
        }
        if (isset($interpretation->fm_pc_ze_max_color)) {
            $measurement->setFmPcZeMaxColor($interpretation->fm_pc_ze_max_color);
        }
        if (isset($interpretation->fm_pc_zf_max)) {
            $measurement->setFmPcZfMax($interpretation->fm_pc_zf_max);
        }
        if (isset($interpretation->fm_pc_zf_max_color)) {
            $measurement->setFmPcZfMaxColor($interpretation->fm_pc_zf_max_color);
        }
        if (isset($interpretation->fm_pc_zone)) {
            $measurement->setFmPcZone($interpretation->fm_pc_zone);
        }
        if (isset($interpretation->tbwffm_pc_100)) {
            $measurement->setTbwffmPc100($interpretation->tbwffm_pc_100);
        }
        if (isset($interpretation->tbwffm_pc_std_a)) {
            $measurement->setTbwffmPcStdA($interpretation->tbwffm_pc_std_a);
        }
        if (isset($interpretation->tbwffm_pc_std_b)) {
            $measurement->setTbwffmPcStdB($interpretation->tbwffm_pc_std_b);
        }
        if (isset($interpretation->tbwffm_pc_std_c)) {
            $measurement->setTbwffmPcStdC($interpretation->tbwffm_pc_std_c);
        }
        if (isset($interpretation->tbwffm_pc_std_d)) {
            $measurement->setTbwffmPcStdD($interpretation->tbwffm_pc_std_d);
        }
        if (isset($interpretation->tbwffm_pc_std_e)) {
            $measurement->setTbwffmPcStdE($interpretation->tbwffm_pc_std_e);
        }
        if (isset($interpretation->tbwffm_pc_std_f)) {
            $measurement->setTbwffmPcStdF($interpretation->tbwffm_pc_std_f);
        }
        if (isset($interpretation->tbwffm_pc_std_g)) {
            $measurement->setTbwffmPcStdG($interpretation->tbwffm_pc_std_g);
        }
        if (isset($interpretation->tbwffm_pc_za_max)) {
            $measurement->setTbwffmPcZaMax($interpretation->tbwffm_pc_za_max);
        }
        if (isset($interpretation->tbwffm_pc_za_max_color)) {
            $measurement->setTbwffmPcZaMaxColor($interpretation->tbwffm_pc_za_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zb_max)) {
            $measurement->setTbwffmPcZbMax($interpretation->tbwffm_pc_zb_max);
        }
        if (isset($interpretation->tbwffm_pc_zb_max_color)) {
            $measurement->setTbwffmPcZbMaxColor($interpretation->tbwffm_pc_zb_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zc_max)) {
            $measurement->setTbwffmPcZcMax($interpretation->tbwffm_pc_zc_max);
        }
        if (isset($interpretation->tbwffm_pc_zc_max_color)) {
            $measurement->setTbwffmPcZcMaxColor($interpretation->tbwffm_pc_zc_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zd_max)) {
            $measurement->setTbwffmPcZdMax($interpretation->tbwffm_pc_zd_max);
        }
        if (isset($interpretation->tbwffm_pc_zd_max_color)) {
            $measurement->setTbwffmPcZdMaxColor($interpretation->tbwffm_pc_zd_max_color);
        }
        if (isset($interpretation->tbwffm_pc_ze_max)) {
            $measurement->setTbwffmPcZeMax($interpretation->tbwffm_pc_ze_max);
        }
        if (isset($interpretation->tbwffm_pc_ze_max_color)) {
            $measurement->setTbwffmPcZeMaxColor($interpretation->tbwffm_pc_ze_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zf_max)) {
            $measurement->setTbwffmPcZfMax($interpretation->tbwffm_pc_zf_max);
        }
        if (isset($interpretation->tbwffm_pc_zf_max_color)) {
            $measurement->setTbwffmPcZfMaxColor($interpretation->tbwffm_pc_zf_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zg_max)) {
            $measurement->setTbwffmPcZgMax($interpretation->tbwffm_pc_zg_max);
        }
        if (isset($interpretation->tbwffm_pc_zg_max_color)) {
            $measurement->setTbwffmPcZgMaxColor($interpretation->tbwffm_pc_zg_max_color);
        }
        if (isset($interpretation->tbwffm_pc_zone)) {
            $measurement->setTbwffmPcZone($interpretation->tbwffm_pc_zone);
        }
        if (isset($interpretation->dffmi)) {
            $measurement->setDffmi($interpretation->dffmi);
        }
        if (isset($interpretation->dffmi_std_a)) {
            $measurement->setDffmiStdA($interpretation->dffmi_std_a);
        }
        if (isset($interpretation->dffmi_std_b)) {
            $measurement->setDffmiStdB($interpretation->dffmi_std_b);
        }
        if (isset($interpretation->dffmi_std_c)) {
            $measurement->setDffmiStdC($interpretation->dffmi_std_c);
        }
        if (isset($interpretation->dffmi_std_d)) {
            $measurement->setDffmiStdD($interpretation->dffmi_std_d);
        }
        if (isset($interpretation->dffmi_za_max)) {
            $measurement->setDffmiZaMax($interpretation->dffmi_za_max);
        }
        if (isset($interpretation->dffmi_za_max_color)) {
            $measurement->setDffmiZaMaxColor($interpretation->dffmi_za_max_color);
        }
        if (isset($interpretation->dffmi_zb_max)) {
            $measurement->setDffmiZbMax($interpretation->dffmi_zb_max);
        }
        if (isset($interpretation->dffmi_zb_max_color)) {
            $measurement->setDffmiZbMaxColor($interpretation->dffmi_zb_max_color);
        }
        if (isset($interpretation->dffmi_zc_max)) {
            $measurement->setDffmiZcMax($interpretation->dffmi_zc_max);
        }
        if (isset($interpretation->dffmi_zc_max_color)) {
            $measurement->setDffmiZcMaxColor($interpretation->dffmi_zc_max_color);
        }
        if (isset($interpretation->dffmi_zd_max)) {
            $measurement->setDffmiZdMax($interpretation->dffmi_zd_max);
        }
        if (isset($interpretation->dffmi_zd_max_color)) {
            $measurement->setDffmiZdMaxColor($interpretation->dffmi_zd_max_color);
        }
        if (isset($interpretation->dffmi_zone)) {
            $measurement->setDffmiZone($interpretation->dffmi_zone);
        }
        if (isset($interpretation->mp_metai)) {
            $measurement->setMpMetai($interpretation->mp_metai);
        }
        if (isset($interpretation->mp_metai_std_a)) {
            $measurement->setMpMetaiStdA($interpretation->mp_metai_std_a);
        }
        if (isset($interpretation->mp_metai_std_b)) {
            $measurement->setMpMetaiStdB($interpretation->mp_metai_std_b);
        }
        if (isset($interpretation->mp_metai_std_c)) {
            $measurement->setMpMetaiStdC($interpretation->mp_metai_std_c);
        }
        if (isset($interpretation->mp_metai_std_d)) {
            $measurement->setMpMetaiStdD($interpretation->mp_metai_std_d);
        }
        if (isset($interpretation->mp_metai_za_max)) {
            $measurement->setMpMetaiZaMax($interpretation->mp_metai_za_max);
        }
        if (isset($interpretation->mp_metai_za_max_color)) {
            $measurement->setMpMetaiZaMaxColor($interpretation->mp_metai_za_max_color);
        }
        if (isset($interpretation->mp_metai_zb_max)) {
            $measurement->setMpMetaiZbMax($interpretation->mp_metai_zb_max);
        }
        if (isset($interpretation->mp_metai_zb_max_color)) {
            $measurement->setMpMetaiZbMaxColor($interpretation->mp_metai_zb_max_color);
        }
        if (isset($interpretation->mp_metai_zc_max)) {
            $measurement->setMpMetaiZcMax($interpretation->mp_metai_zc_max);
        }
        if (isset($interpretation->mp_metai_zc_max_color)) {
            $measurement->setMpMetaiZcMaxColor($interpretation->mp_metai_zc_max_color);
        }
        if (isset($interpretation->mp_metai_zd_max)) {
            $measurement->setMpMetaiZdMax($interpretation->mp_metai_zd_max);
        }
        if (isset($interpretation->mp_metai_zd_max_color)) {
            $measurement->setMpMetaiZdMaxColor($interpretation->mp_metai_zd_max_color);
        }
        if (isset($interpretation->mp_metai_zone)) {
            $measurement->setMpMetaiZone($interpretation->mp_metai_zone);
        }
        if (isset($interpretation->ffmi)) {
            $measurement->setFfmi($interpretation->ffmi);
        }
        if (isset($interpretation->iffmi)) {
            $measurement->setIffmi($interpretation->iffmi);
        }
        if (isset($interpretation->iffmi_std_a)) {
            $measurement->setIffmiStdA($interpretation->iffmi_std_a);
        }
        if (isset($interpretation->iffmi_std_b)) {
            $measurement->setIffmiStdB($interpretation->iffmi_std_b);
        }
        if (isset($interpretation->iffmi_std_c)) {
            $measurement->setIffmiStdC($interpretation->iffmi_std_c);
        }
        if (isset($interpretation->iffmi_std_d)) {
            $measurement->setIffmiStdD($interpretation->iffmi_std_d);
        }
        if (isset($interpretation->iffmi_za_max)) {
            $measurement->setIffmiZaMax($interpretation->iffmi_za_max);
        }
        if (isset($interpretation->iffmi_za_max_color)) {
            $measurement->setIffmiZaMaxColor($interpretation->iffmi_za_max_color);
        }
        if (isset($interpretation->iffmi_zb_max)) {
            $measurement->setIffmiZbMax($interpretation->iffmi_zb_max);
        }
        if (isset($interpretation->iffmi_zb_max_color)) {
            $measurement->setIffmiZbMaxColor($interpretation->iffmi_zb_max_color);
        }
        if (isset($interpretation->iffmi_zc_max)) {
            $measurement->setIffmiZcMax($interpretation->iffmi_zc_max);
        }
        if (isset($interpretation->iffmi_zc_max_color)) {
            $measurement->setIffmiZcMaxColor($interpretation->iffmi_zc_max_color);
        }
        if (isset($interpretation->iffmi_zd_max)) {
            $measurement->setIffmiZdMax($interpretation->iffmi_zd_max);
        }
        if (isset($interpretation->iffmi_zd_max_color)) {
            $measurement->setIffmiZdMaxColor($interpretation->iffmi_zd_max_color);
        }
        if (isset($interpretation->iffmi_zone)) {
            $measurement->setIffmiZone($interpretation->iffmi_zone);
        }
        if (isset($interpretation->bmri)) {
            $measurement->setBmri($interpretation->bmri);
        }
        if (isset($interpretation->bmri_std_a)) {
            $measurement->setBmriStdA($interpretation->bmri_std_a);
        }
        if (isset($interpretation->bmri_std_b)) {
            $measurement->setBmriStdB($interpretation->bmri_std_b);
        }
        if (isset($interpretation->bmri_std_c)) {
            $measurement->setBmriStdC($interpretation->bmri_std_c);
        }
        if (isset($interpretation->bmri_std_d)) {
            $measurement->setBmriStdD($interpretation->bmri_std_d);
        }
        if (isset($interpretation->bmri_za_max)) {
            $measurement->setBmriZaMax($interpretation->bmri_za_max);
        }
        if (isset($interpretation->bmri_za_max_color)) {
            $measurement->setBmriZaMaxColor($interpretation->bmri_za_max_color);
        }
        if (isset($interpretation->bmri_zb_max)) {
            $measurement->setBmriZbMax($interpretation->bmri_zb_max);
        }
        if (isset($interpretation->bmri_zb_max_color)) {
            $measurement->setBmriZbMaxColor($interpretation->bmri_zb_max_color);
        }
        if (isset($interpretation->bmri_zc_max)) {
            $measurement->setBmriZcMax($interpretation->bmri_zc_max);
        }
        if (isset($interpretation->bmri_zc_max_color)) {
            $measurement->setBmriZcMaxColor($interpretation->bmri_zc_max_color);
        }
        if (isset($interpretation->bmri_zd_max)) {
            $measurement->setBmriZdMax($interpretation->bmri_zd_max);
        }
        if (isset($interpretation->bmri_zd_max_color)) {
            $measurement->setBmriZdMaxColor($interpretation->bmri_zd_max_color);
        }
        if (isset($interpretation->bmri_zone)) {
            $measurement->setBmriZone($interpretation->bmri_zone);
        }
        if (isset($interpretation->ffecw_pc_100)) {
            $measurement->setFfecwPc100($interpretation->ffecw_pc_100);
        }
        if (isset($interpretation->ffecwi)) {
            $measurement->setFfecwi($interpretation->ffecwi);
        }
        if (isset($interpretation->ffecw_pc_std_a)) {
            $measurement->setFfecwPcStdA($interpretation->ffecw_pc_std_a);
        }
        if (isset($interpretation->ffecw_pc_std_b)) {
            $measurement->setFfecwPcStdB($interpretation->ffecw_pc_std_b);
        }
        if (isset($interpretation->ffecw_pc_std_c)) {
            $measurement->setFfecwPcStdC($interpretation->ffecw_pc_std_c);
        }
        if (isset($interpretation->ffecw_pc_std_d)) {
            $measurement->setFfecwPcStdD($interpretation->ffecw_pc_std_d);
        }
        if (isset($interpretation->ffecw_pc_std_e)) {
            $measurement->setFfecwPcStdE($interpretation->ffecw_pc_std_e);
        }
        if (isset($interpretation->ffecw_pc_std_f)) {
            $measurement->setFfecwPcStdF($interpretation->ffecw_pc_std_f);
        }
        if (isset($interpretation->ffecw_pc_std_g)) {
            $measurement->setFfecwPcStdG($interpretation->ffecw_pc_std_g);
        }
        if (isset($interpretation->ffecwi_std_a)) {
            $measurement->setFfecwiStdA($interpretation->ffecwi_std_a);
        }
        if (isset($interpretation->ffecwi_std_b)) {
            $measurement->setFfecwiStdB($interpretation->ffecwi_std_b);
        }
        if (isset($interpretation->ffecwi_std_c)) {
            $measurement->setFfecwiStdC($interpretation->ffecwi_std_c);
        }
        if (isset($interpretation->ffecwi_std_d)) {
            $measurement->setFfecwiStdD($interpretation->ffecwi_std_d);
        }
        if (isset($interpretation->ffecwi_std_e)) {
            $measurement->setFfecwiStdE($interpretation->ffecwi_std_e);
        }
        if (isset($interpretation->ffecwi_std_f)) {
            $measurement->setFfecwiStdF($interpretation->ffecwi_std_f);
        }
        if (isset($interpretation->ffecwi_std_g)) {
            $measurement->setFfecwiStdG($interpretation->ffecwi_std_g);
        }
        if (isset($interpretation->ffecw_pc_za_max)) {
            $measurement->setFfecwPcZaMax($interpretation->ffecw_pc_za_max);
        }
        if (isset($interpretation->ffecw_pc_za_max_color)) {
            $measurement->setFfecwPcZaMaxColor($interpretation->ffecw_pc_za_max_color);
        }
        if (isset($interpretation->ffecw_pc_zb_max)) {
            $measurement->setFfecwPcZbMax($interpretation->ffecw_pc_zb_max);
        }
        if (isset($interpretation->ffecw_pc_zb_max_color)) {
            $measurement->setFfecwPcZbMaxColor($interpretation->ffecw_pc_zb_max_color);
        }
        if (isset($interpretation->ffecw_pc_zc_max)) {
            $measurement->setFfecwPcZcMax($interpretation->ffecw_pc_zc_max);
        }
        if (isset($interpretation->ffecw_pc_zc_max_color)) {
            $measurement->setFfecwPcZcMaxColor($interpretation->ffecw_pc_zc_max_color);
        }
        if (isset($interpretation->ffecw_pc_zd_max)) {
            $measurement->setFfecwPcZdMax($interpretation->ffecw_pc_zd_max);
        }
        if (isset($interpretation->ffecw_pc_zd_max_color)) {
            $measurement->setFfecwPcZdMaxColor($interpretation->ffecw_pc_zd_max_color);
        }
        if (isset($interpretation->ffecw_pc_ze_max)) {
            $measurement->setFfecwPcZeMax($interpretation->ffecw_pc_ze_max);
        }
        if (isset($interpretation->ffecw_pc_ze_max_color)) {
            $measurement->setFfecwPcZeMaxColor($interpretation->ffecw_pc_ze_max_color);
        }
        if (isset($interpretation->ffecw_pc_zf_max)) {
            $measurement->setFfecwPcZfMax($interpretation->ffecw_pc_zf_max);
        }
        if (isset($interpretation->ffecw_pc_zf_max_color)) {
            $measurement->setFfecwPcZfMaxColor($interpretation->ffecw_pc_zf_max_color);
        }
        if (isset($interpretation->ffecw_pc_zg_max)) {
            $measurement->setFfecwPcZgMax($interpretation->ffecw_pc_zg_max);
        }
        if (isset($interpretation->ffecw_pc_zg_max_color)) {
            $measurement->setFfecwPcZgMaxColor($interpretation->ffecw_pc_zg_max_color);
        }
        if (isset($interpretation->ffecw_pc_zone)) {
            $measurement->setFfecwPcZone($interpretation->ffecw_pc_zone);
        }
        if (isset($interpretation->fficw_pc_100)) {
            $measurement->setFficwPc100($interpretation->fficw_pc_100);
        }
        if (isset($interpretation->fficwi)) {
            $measurement->setFficwi($interpretation->fficwi);
        }
        if (isset($interpretation->fficw_pc_std_a)) {
            $measurement->setFficwPcStdA($interpretation->fficw_pc_std_a);
        }
        if (isset($interpretation->fficw_pc_std_b)) {
            $measurement->setFficwPcStdB($interpretation->fficw_pc_std_b);
        }
        if (isset($interpretation->fficw_pc_std_c)) {
            $measurement->setFficwPcStdC($interpretation->fficw_pc_std_c);
        }
        if (isset($interpretation->fficw_pc_std_d)) {
            $measurement->setFficwPcStdD($interpretation->fficw_pc_std_d);
        }
        if (isset($interpretation->fficw_pc_std_e)) {
            $measurement->setFficwPcStdE($interpretation->fficw_pc_std_e);
        }
        if (isset($interpretation->fficw_pc_std_f)) {
            $measurement->setFficwPcStdF($interpretation->fficw_pc_std_f);
        }
        if (isset($interpretation->fficw_pc_std_g)) {
            $measurement->setFficwPcStdG($interpretation->fficw_pc_std_g);
        }
        if (isset($interpretation->fficwi_std_a)) {
            $measurement->setFficwiStdA($interpretation->fficwi_std_a);
        }
        if (isset($interpretation->fficwi_std_b)) {
            $measurement->setFficwiStdB($interpretation->fficwi_std_b);
        }
        if (isset($interpretation->fficwi_std_c)) {
            $measurement->setFficwiStdC($interpretation->fficwi_std_c);
        }
        if (isset($interpretation->fficwi_std_d)) {
            $measurement->setFficwiStdD($interpretation->fficwi_std_d);
        }
        if (isset($interpretation->fficwi_std_e)) {
            $measurement->setFficwiStdE($interpretation->fficwi_std_e);
        }
        if (isset($interpretation->fficwi_std_f)) {
            $measurement->setFficwiStdF($interpretation->fficwi_std_f);
        }
        if (isset($interpretation->fficwi_std_g)) {
            $measurement->setFficwiStdG($interpretation->fficwi_std_g);
        }
        if (isset($interpretation->fficw_pc_za_max)) {
            $measurement->setFficwPcZaMax($interpretation->fficw_pc_za_max);
        }
        if (isset($interpretation->fficw_pc_za_max_color)) {
            $measurement->setFficwPcZaMaxColor($interpretation->fficw_pc_za_max_color);
        }
        if (isset($interpretation->fficw_pc_zb_max)) {
            $measurement->setFficwPcZbMax($interpretation->fficw_pc_zb_max);
        }
        if (isset($interpretation->fficw_pc_zb_max_color)) {
            $measurement->setFficwPcZbMaxColor($interpretation->fficw_pc_zb_max_color);
        }
        if (isset($interpretation->fficw_pc_zc_max)) {
            $measurement->setFficwPcZcMax($interpretation->fficw_pc_zc_max);
        }
        if (isset($interpretation->fficw_pc_zc_max_color)) {
            $measurement->setFficwPcZcMaxColor($interpretation->fficw_pc_zc_max_color);
        }
        if (isset($interpretation->fficw_pc_zd_max)) {
            $measurement->setFficwPcZdMax($interpretation->fficw_pc_zd_max);
        }
        if (isset($interpretation->fficw_pc_zd_max_color)) {
            $measurement->setFficwPcZdMaxColor($interpretation->fficw_pc_zd_max_color);
        }
        if (isset($interpretation->fficw_pc_ze_max)) {
            $measurement->setFficwPcZeMax($interpretation->fficw_pc_ze_max);
        }
        if (isset($interpretation->fficw_pc_ze_max_color)) {
            $measurement->setFficwPcZeMaxColor($interpretation->fficw_pc_ze_max_color);
        }
        if (isset($interpretation->fficw_pc_zf_max)) {
            $measurement->setFficwPcZfMax($interpretation->fficw_pc_zf_max);
        }
        if (isset($interpretation->fficw_pc_zf_max_color)) {
            $measurement->setFficwPcZfMaxColor($interpretation->fficw_pc_zf_max_color);
        }
        if (isset($interpretation->fficw_pc_zg_max)) {
            $measurement->setFficwPcZgMax($interpretation->fficw_pc_zg_max);
        }
        if (isset($interpretation->fficw_pc_zg_max_color)) {
            $measurement->setFficwPcZgMaxColor($interpretation->fficw_pc_zg_max_color);
        }
        if (isset($interpretation->fficw_pc_zone)) {
            $measurement->setFficwPcZone($interpretation->fficw_pc_zone);
        }
        if (isset($interpretation->asmhi_std_a)) {
            $measurement->setAsmhiStdA($interpretation->asmhi_std_a);
        }
        if (isset($interpretation->asmhi_std_b)) {
            $measurement->setAsmhiStdB($interpretation->asmhi_std_b);
        }
        if (isset($interpretation->asmhi_std_c)) {
            $measurement->setAsmhiStdC($interpretation->asmhi_std_c);
        }
        if (isset($interpretation->asmhi_std_d)) {
            $measurement->setAsmhiStdD($interpretation->asmhi_std_d);
        }
        if (isset($interpretation->asmhi_za_max)) {
            $measurement->setAsmhiZaMax($interpretation->asmhi_za_max);
        }
        if (isset($interpretation->asmhi_za_max_color)) {
            $measurement->setAsmhiZaMaxColor($interpretation->asmhi_za_max_color);
        }
        if (isset($interpretation->asmhi_zb_max)) {
            $measurement->setAsmhiZbMax($interpretation->asmhi_zb_max);
        }
        if (isset($interpretation->asmhi_zb_max_color)) {
            $measurement->setAsmhiZbMaxColor($interpretation->asmhi_zb_max_color);
        }
        if (isset($interpretation->asmhi_zc_max)) {
            $measurement->setAsmhiZcMax($interpretation->asmhi_zc_max);
        }
        if (isset($interpretation->asmhi_zc_max_color)) {
            $measurement->setAsmhiZcMaxColor($interpretation->asmhi_zc_max_color);
        }
        if (isset($interpretation->asmhi_zd_max)) {
            $measurement->setAsmhiZdMax($interpretation->asmhi_zd_max);
        }
        if (isset($interpretation->asmhi_zd_max_color)) {
            $measurement->setAsmhiZdMaxColor($interpretation->asmhi_zd_max_color);
        }
        if (isset($interpretation->asmhi_zone)) {
            $measurement->setAsmhiZone($interpretation->asmhi_zone);
        }
        if (isset($interpretation->bcmi)) {
            $measurement->setBcmi($interpretation->bcmi);
        }
        if (isset($interpretation->bcmi_std_a)) {
            $measurement->setBcmiStdA($interpretation->bcmi_std_a);
        }
        if (isset($interpretation->bcmi_std_b)) {
            $measurement->setBcmiStdB($interpretation->bcmi_std_b);
        }
        if (isset($interpretation->bcmi_std_c)) {
            $measurement->setBcmiStdC($interpretation->bcmi_std_c);
        }
        if (isset($interpretation->bcmi_std_d)) {
            $measurement->setBcmiStdD($interpretation->bcmi_std_d);
        }
        if (isset($interpretation->bcmi_za_max)) {
            $measurement->setBcmiZaMax($interpretation->bcmi_za_max);
        }
        if (isset($interpretation->bcmi_za_max_color)) {
            $measurement->setBcmiZaMaxColor($interpretation->bcmi_za_max_color);
        }
        if (isset($interpretation->bcmi_zb_max)) {
            $measurement->setBcmiZbMax($interpretation->bcmi_zb_max);
        }
        if (isset($interpretation->bcmi_zb_max_color)) {
            $measurement->setBcmiZbMaxColor($interpretation->bcmi_zb_max_color);
        }
        if (isset($interpretation->bcmi_zc_max)) {
            $measurement->setBcmiZcMax($interpretation->bcmi_zc_max);
        }
        if (isset($interpretation->bcmi_zc_max_color)) {
            $measurement->setBcmiZcMaxColor($interpretation->bcmi_zc_max_color);
        }
        if (isset($interpretation->bcmi_zd_max)) {
            $measurement->setBcmiZdMax($interpretation->bcmi_zd_max);
        }
        if (isset($interpretation->bcmi_zd_max_color)) {
            $measurement->setBcmiZdMaxColor($interpretation->bcmi_zd_max_color);
        }
        if (isset($interpretation->bcmi_zone)) {
            $measurement->setBcmiZone($interpretation->bcmi_zone);
        }
        if (isset($interpretation->imc_std_a)) {
            $measurement->setImcStdA($interpretation->imc_std_a);
        }
        if (isset($interpretation->imc_std_b)) {
            $measurement->setImcStdB($interpretation->imc_std_b);
        }
        if (isset($interpretation->imc_std_c)) {
            $measurement->setImcStdC($interpretation->imc_std_c);
        }
        if (isset($interpretation->imc_std_d)) {
            $measurement->setImcStdD($interpretation->imc_std_d);
        }
        if (isset($interpretation->imc_std_e)) {
            $measurement->setImcStdE($interpretation->imc_std_e);
        }
        if (isset($interpretation->imc_std_f)) {
            $measurement->setImcStdF($interpretation->imc_std_f);
        }
        if (isset($interpretation->imc_std_g)) {
            $measurement->setImcStdG($interpretation->imc_std_g);
        }
        if (isset($interpretation->imc_za_max)) {
            $measurement->setImcZaMax($interpretation->imc_za_max);
        }
        if (isset($interpretation->imc_za_max_color)) {
            $measurement->setImcZaMaxColor($interpretation->imc_za_max_color);
        }
        if (isset($interpretation->imc_zb_max)) {
            $measurement->setImcZbMax($interpretation->imc_zb_max);
        }
        if (isset($interpretation->imc_zb_max_color)) {
            $measurement->setImcZbMaxColor($interpretation->imc_zb_max_color);
        }
        if (isset($interpretation->imc_zc_max)) {
            $measurement->setImcZcMax($interpretation->imc_zc_max);
        }
        if (isset($interpretation->imc_zc_max_color)) {
            $measurement->setImcZcMaxColor($interpretation->imc_zc_max_color);
        }
        if (isset($interpretation->imc_zd_max)) {
            $measurement->setImcZdMax($interpretation->imc_zd_max);
        }
        if (isset($interpretation->imc_zd_max_color)) {
            $measurement->setImcZdMaxColor($interpretation->imc_zd_max_color);
        }
        if (isset($interpretation->imc_ze_max)) {
            $measurement->setImcZeMax($interpretation->imc_ze_max);
        }
        if (isset($interpretation->imc_ze_max_color)) {
            $measurement->setImcZeMaxColor($interpretation->imc_ze_max_color);
        }
        if (isset($interpretation->imc_zf_max)) {
            $measurement->setImcZfMax($interpretation->imc_zf_max);
        }
        if (isset($interpretation->imc_zf_max_color)) {
            $measurement->setImcZfMaxColor($interpretation->imc_zf_max_color);
        }
        if (isset($interpretation->imc_zg_max)) {
            $measurement->setImcZgMax($interpretation->imc_zg_max);
        }
        if (isset($interpretation->imc_zg_max_color)) {
            $measurement->setImcZgMaxColor($interpretation->imc_zg_max_color);
        }
        if (isset($interpretation->imc_zone)) {
            $measurement->setImcZone($interpretation->imc_zone);
        }
        if (isset($interpretation->fmslmir_za_max)) {
            $measurement->setFmslmirZaMax($interpretation->fmslmir_za_max);
        }
        if (isset($interpretation->fmslmir_za_max_color)) {
            $measurement->setFmslmirZaMaxColor($interpretation->fmslmir_za_max_color);
        }
        if (isset($interpretation->fmslmir_zb_max)) {
            $measurement->setFmslmirZbMax($interpretation->fmslmir_zb_max);
        }
        if (isset($interpretation->fmslmir_zb_max_color)) {
            $measurement->setFmslmirZbMaxColor($interpretation->fmslmir_zb_max_color);
        }
        if (isset($interpretation->fmslmir_zone)) {
            $measurement->setFmslmirZone($interpretation->fmslmir_zone);
        }
        if (isset($interpretation->fmir_za_max)) {
            $measurement->setFmirZaMax($interpretation->fmir_za_max);
        }
        if (isset($interpretation->fmir_za_max_color)) {
            $measurement->setFmirZaMaxColor($interpretation->fmir_za_max_color);
        }
        if (isset($interpretation->fmir_zb_max)) {
            $measurement->setFmirZbMax($interpretation->fmir_zb_max);
        }
        if (isset($interpretation->fmir_zb_max_color)) {
            $measurement->setFmirZbMaxColor($interpretation->fmir_zb_max_color);
        }
        if (isset($interpretation->fmir_zone)) {
            $measurement->setFmirZone($interpretation->fmir_zone);
        }
        if (isset($interpretation->slmir_za_max)) {
            $measurement->setSlmirZaMax($interpretation->slmir_za_max);
        }
        if (isset($interpretation->slmir_za_max_color)) {
            $measurement->setSlmirZaMaxColor($interpretation->slmir_za_max_color);
        }
        if (isset($interpretation->slmir_zb_max)) {
            $measurement->setSlmirZbMax($interpretation->slmir_zb_max);
        }
        if (isset($interpretation->slmir_zb_max_color)) {
            $measurement->setSlmirZbMaxColor($interpretation->slmir_zb_max_color);
        }
        if (isset($interpretation->slmir_zone)) {
            $measurement->setSlmirZone($interpretation->slmir_zone);
        }
        if (isset($interpretation->whr_za_max)) {
            $measurement->setWhrZaMax($interpretation->whr_za_max);
        }
        if (isset($interpretation->whr_za_max_color)) {
            $measurement->setWhrZaMaxColor($interpretation->whr_za_max_color);
        }
        if (isset($interpretation->whr_zb_max)) {
            $measurement->setWhrZbMax($interpretation->whr_zb_max);
        }
        if (isset($interpretation->whr_zb_max_color)) {
            $measurement->setWhrZbMaxColor($interpretation->whr_zb_max_color);
        }
        if (isset($interpretation->whr_zone)) {
            $measurement->setWhrZone($interpretation->whr_zone);
        }
        if (isset($interpretation->whtr_za_max)) {
            $measurement->setWhtrZaMax($interpretation->whtr_za_max);
        }
        if (isset($interpretation->whtr_za_max_color)) {
            $measurement->setWhtrZaMaxColor($interpretation->whtr_za_max_color);
        }
        if (isset($interpretation->whtr_zb_max)) {
            $measurement->setWhtrZbMax($interpretation->whtr_zb_max);
        }
        if (isset($interpretation->whtr_zb_max_color)) {
            $measurement->setWhtrZbMaxColor($interpretation->whtr_zb_max_color);
        }
        if (isset($interpretation->whtr_zone)) {
            $measurement->setWhtrZone($interpretation->whtr_zone);
        }
        if (isset($interpretation->total_cc_sc_za_max)) {
            $measurement->setTotalCcScZaMax($interpretation->total_cc_sc_za_max);
        }
        if (isset($interpretation->total_cc_sc_za_max_color)) {
            $measurement->setTotalCcScZaMaxColor($interpretation->total_cc_sc_za_max_color);
        }
        if (isset($interpretation->total_cc_sc_zb_max)) {
            $measurement->setTotalCcScZbMax($interpretation->total_cc_sc_zb_max);
        }
        if (isset($interpretation->total_cc_sc_zb_max_color)) {
            $measurement->setTotalCcScZbMaxColor($interpretation->total_cc_sc_zb_max_color);
        }
        if (isset($interpretation->total_cc_sc_zc_max)) {
            $measurement->setTotalCcScZcMax($interpretation->total_cc_sc_zc_max);
        }
        if (isset($interpretation->total_cc_sc_zc_max_color)) {
            $measurement->setTotalCcScZcMaxColor($interpretation->total_cc_sc_zc_max_color);
        }
        if (isset($interpretation->total_cc_sc_zone)) {
            $measurement->setTotalCcScZone($interpretation->total_cc_sc_zone);
        }
        if (isset($interpretation->total_muh_sc_za_max)) {
            $measurement->setTotalMuhScZaMax($interpretation->total_muh_sc_za_max);
        }
        if (isset($interpretation->total_muh_sc_za_max_color)) {
            $measurement->setTotalMuhScZaMaxColor($interpretation->total_muh_sc_za_max_color);
        }
        if (isset($interpretation->total_muh_sc_zb_max)) {
            $measurement->setTotalMuhScZbMax($interpretation->total_muh_sc_zb_max);
        }
        if (isset($interpretation->total_muh_sc_zb_max_color)) {
            $measurement->setTotalMuhScZbMaxColor($interpretation->total_muh_sc_zb_max_color);
        }
        if (isset($interpretation->total_muh_sc_zc_max)) {
            $measurement->setTotalMuhScZcMax($interpretation->total_muh_sc_zc_max);
        }
        if (isset($interpretation->total_muh_sc_zc_max_color)) {
            $measurement->setTotalMuhScZcMaxColor($interpretation->total_muh_sc_zc_max_color);
        }
        if (isset($interpretation->total_muh_sc_zone)) {
            $measurement->setTotalMuhScZone($interpretation->total_muh_sc_zone);
        }
        if (isset($interpretation->cible_za_color)) {
            $measurement->setCibleZaColor($interpretation->cible_za_color);
        }
        if (isset($interpretation->cible_zb_color)) {
            $measurement->setCibleZbColor($interpretation->cible_zb_color);
        }
        if (isset($interpretation->cible_zc_color)) {
            $measurement->setCibleZcColor($interpretation->cible_zc_color);
        }
        if (isset($interpretation->cible_zd_color)) {
            $measurement->setCibleZdColor($interpretation->cible_zd_color);
        }
        if (isset($interpretation->cible_ze_color)) {
            $measurement->setCibleZeColor($interpretation->cible_ze_color);
        }
        if (isset($interpretation->cible_zf_color)) {
            $measurement->setCibleZfColor($interpretation->cible_zf_color);
        }
        if (isset($interpretation->cible_zone)) {
            $measurement->setCibleZone($interpretation->cible_zone);
        }
        if (isset($interpretation->cible_point)) {
            $measurement->setCiblePoint($interpretation->cible_point);
        }
        if (isset($interpretation->cible_icw_pc_std_b)) {
            $measurement->setCibleIcwPcStdB($interpretation->cible_icw_pc_std_b);
        }
        if (isset($interpretation->cible_icw_pc_std_c)) {
            $measurement->setCibleIcwPcStdC($interpretation->cible_icw_pc_std_c);
        }
        if (isset($interpretation->cible_icw_pc_std_d)) {
            $measurement->setCibleIcwPcStdD($interpretation->cible_icw_pc_std_d);
        }
        if (isset($interpretation->cible_icw_pc_std_e)) {
            $measurement->setCibleIcwPcStdE($interpretation->cible_icw_pc_std_e);
        }
        if (isset($interpretation->cible_fm_hc_pc_std_a)) {
            $measurement->setCibleFmHcPcStdA($interpretation->cible_fm_hc_pc_std_a);
        }
        if (isset($interpretation->cible_fm_hc_pc_std_b)) {
            $measurement->setCibleFmHcPcStdB($interpretation->cible_fm_hc_pc_std_b);
        }
        if (isset($interpretation->cible_fm_hc_pc_std_c)) {
            $measurement->setCibleFmHcPcStdC($interpretation->cible_fm_hc_pc_std_c);
        }
        if (isset($interpretation->cible_fm_hc_pc_std_e)) {
            $measurement->setCibleFmHcPcStdE($interpretation->cible_fm_hc_pc_std_e);
        }
        if (isset($interpretation->cible_ffw_std_a)) {
            $measurement->setCibleFfwStdA($interpretation->cible_ffw_std_a);
        }
        if (isset($interpretation->cible_ffw_std_b)) {
            $measurement->setCibleFfwStdB($interpretation->cible_ffw_std_b);
        }
        if (isset($interpretation->cible_ffw_std_c)) {
            $measurement->setCibleFfwStdC($interpretation->cible_ffw_std_c);
        }
        if (isset($interpretation->cible_ffw_std_d)) {
            $measurement->setCibleFfwStdD($interpretation->cible_ffw_std_d);
        }
        if (isset($interpretation->fm_hc_pc_pos)) {
            $measurement->setFmHcPcPos($interpretation->fm_hc_pc_pos);
        }
        if (isset($interpretation->ffw_pc_pos)) {
            $measurement->setFfwPcPos($interpretation->ffw_pc_pos);
        }
        if (isset($interpretation->mmhi_pos)) {
            $measurement->setMmhiPos($interpretation->mmhi_pos);
        }
        if (isset($interpretation->adcr_pos)) {
            $measurement->setAdcrPos($interpretation->adcr_pos);
        }
        if (isset($interpretation->adcr_cons_inf)) {
            $measurement->setAdcrConsInf($interpretation->adcr_cons_inf);
        }
        if (isset($interpretation->adcr_cons_sup)) {
            $measurement->setAdcrConsSup($interpretation->adcr_cons_sup);
        }
        if (isset($interpretation->asmmi_pos)) {
            $measurement->setAsmmiPos($interpretation->asmmi_pos);
        }
        if (isset($interpretation->ecw_pc_pos)) {
            $measurement->setEcwPcPos($interpretation->ecw_pc_pos);
        }
        if (isset($interpretation->icw_pc_pos)) {
            $measurement->setIcwPcPos($interpretation->icw_pc_pos);
        }
        if (isset($interpretation->fm_pc_pos)) {
            $measurement->setFmPcPos($interpretation->fm_pc_pos);
        }
        if (isset($interpretation->tbwffm_pc_pos)) {
            $measurement->setTbwffmPcPos($interpretation->tbwffm_pc_pos);
        }
        if (isset($interpretation->dffmi_pos)) {
            $measurement->setDffmiPos($interpretation->dffmi_pos);
        }
        if (isset($interpretation->mp_metai_pos)) {
            $measurement->setMpMetaiPos($interpretation->mp_metai_pos);
        }
        if (isset($interpretation->iffmi_pos)) {
            $measurement->setIffmiPos($interpretation->iffmi_pos);
        }
        if (isset($interpretation->bmri_pos)) {
            $measurement->setBmriPos($interpretation->bmri_pos);
        }
        if (isset($interpretation->ffecw_pc_pos)) {
            $measurement->setFfecwPcPos($interpretation->ffecw_pc_pos);
        }
        if (isset($interpretation->ffecwi_pos)) {
            $measurement->setFfecwiPos($interpretation->ffecwi_pos);
        }
        if (isset($interpretation->fficw_pc_pos)) {
            $measurement->setFficwPcPos($interpretation->fficw_pc_pos);
        }
        if (isset($interpretation->fficwi_pos)) {
            $measurement->setFficwiPos($interpretation->fficwi_pos);
        }
        if (isset($interpretation->asmhi_pos)) {
            $measurement->setAsmhiPos($interpretation->asmhi_pos);
        }
        if (isset($interpretation->bcmi_pos)) {
            $measurement->setBcmiPos($interpretation->bcmi_pos);
        }
        if (isset($interpretation->imc_pos)) {
            $measurement->setImcPos($interpretation->imc_pos);
        }
        if (isset($interpretation->fmslmir_cc_sc)) {
            $measurement->setFmslmirCcSc($interpretation->fmslmir_cc_sc);
        }
        if (isset($interpretation->fmir_cc_sc)) {
            $measurement->setFmirCcSc($interpretation->fmir_cc_sc);
        }
        if (isset($interpretation->slmir_cc_sc)) {
            $measurement->setSlmirCcSc($interpretation->slmir_cc_sc);
        }
        if (isset($interpretation->whr_cc_sc)) {
            $measurement->setWhrCcSc($interpretation->whr_cc_sc);
        }
        if (isset($interpretation->whtr_cc_sc)) {
            $measurement->setWhtrCcSc($interpretation->whtr_cc_sc);
        }
        if (isset($interpretation->total_cc_sc)) {
            $measurement->setTotalCcSc($interpretation->total_cc_sc);
        }
        if (isset($interpretation->total_cc_sc_pos)) {
            $measurement->setTotalCcScPos($interpretation->total_cc_sc_pos);
        }
        if (isset($interpretation->fmslmir_muh_sc)) {
            $measurement->setFmslmirMuhSc($interpretation->fmslmir_muh_sc);
        }
        if (isset($interpretation->fmir_muh_sc)) {
            $measurement->setFmirMuhSc($interpretation->fmir_muh_sc);
        }
        if (isset($interpretation->slmir_muh_sc)) {
            $measurement->setSlmirMuhSc($interpretation->slmir_muh_sc);
        }
        if (isset($interpretation->total_muh_sc)) {
            $measurement->setTotalMuhSc($interpretation->total_muh_sc);
        }
        if (isset($interpretation->total_muh_sc_pos)) {
            $measurement->setTotalMuhScPos($interpretation->total_muh_sc_pos);
        }
        if (isset($interpretation->cible_icw_pc_pos)) {
            $measurement->setCibleIcwPcPos($interpretation->cible_icw_pc_pos);
        }
        if (isset($interpretation->cible_imc_pos)) {
            $measurement->setCibleImcPos($interpretation->cible_imc_pos);
        }
        if (isset($interpretation->cible_fm_hc_pc_pos)) {
            $measurement->setCibleFmHcPcPos($interpretation->cible_fm_hc_pc_pos);
        }
        if (isset($interpretation->cible_mmhi_pos)) {
            $measurement->setCibleMmhiPos($interpretation->cible_mmhi_pos);
        }
        if (isset($interpretation->cible_asmhi_pos)) {
            $measurement->setCibleAsmhiPos($interpretation->cible_asmhi_pos);
        }
        if (isset($interpretation->cible_ffw_pos)) {
            $measurement->setCibleFfwPos($interpretation->cible_ffw_pos);
        }
        if (isset($interpretation->asmli_color)) {
            $measurement->setAsmliColor($interpretation->asmli_color);
        }
        if (isset($interpretation->asmtli_color)) {
            $measurement->setAsmtliColor($interpretation->asmtli_color);
        }
    }
}
