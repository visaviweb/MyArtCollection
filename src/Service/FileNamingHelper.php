<?php

namespace App\Service;

class FileNamingHelper
{

    private $techniques = array(
        'olio su tela',
        'olio su tavola',
        'pastel su carta',
        'aquarello su carta',
        'matita su carta',
        'carboncino su carta',
        'tecnica mista su tela',
        'tecnica mista su carta',
        'tecnica mista',
    );

    public function getDataFromFilename(string $filename, array $artistList) : array
    {
        $data = array();
        if (preg_match('/^(.+_)?T_([^_]+)_/', $filename, $matches)) {
            // test title like: A_Velasquez_d_1687_T_Titolo dell'opera_t_huile sur toile_d_34,7x34_l_Musée d'Orsay_.jpg
            $data['title'] = $matches[2];
            $data['artist'] = (preg_match('/^(.+_)?A_([^_]+)_/i', $filename, $matches)) ? $matches[2] : '';
            $data['date'] = (preg_match('/^(.+_)?D_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
            $data['technique'] = (preg_match('/^(.+_)?t_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
            $data['dimensions'] = (preg_match('/^(.+_)?d_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
            $data['place'] = (preg_match('/^(.+_)?l_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        } elseif (preg_match(
            '/' .
            '(([^,]+)(,\s(.*))?\s)?' . // autor
            '((ca\.\s?)?\d{4}(-\d{2,})?)\s' . // date
            '(.+)\s' . // title
            '(\d{2,}((,|.)\d)*x\d{2,}((,|.)\d)*)\s' . // dimension
            '(.*)'.
            '/', // place
            $filename,
            $matches
        )) {
            // test title like: [Autor] [date] [title] [dimension] [place]
            // where [Autor] is not required
            // [date] may have be "1678" "1678-79" "1899-1900" "ca.1899"
            // dimension "24x40" "34,5x44,7"

            $data['artist'] = $matches[1];
            $data['date'] = $matches[5];
            $data['title'] = $matches[8];
            $data['technique'] = '';
            $data['dimensions'] = $matches[9];
            $data['place'] = $matches[14];
        } else {
            // test title like: Manet, Edouard_1899_La Femme au perroquet_ot_185x126_Metropolitan Museum of Art.jpg
            $temp = explode('_', $filename);
            $data['artist'] = (!empty($temp[0])) ? $temp[0] : '';
            $data['date'] = (!empty($temp[1])) ? $temp[1] : '';
            $data['title'] = (!empty($temp[2])) ? $temp[2] : '';
            $data['technique'] = (!empty($temp[3])) ? $temp[3] : '';
            $data['dimensions'] = (!empty($temp[4])) ? $temp[4] : '';
            $data['place'] = (!empty($temp[5])) ? $temp[5] : '';
        }
        if (array_search($data['artist'], $artistList) === false) {
            $data['new_artist'] = $data['artist'];
        }
        if (array_search($data['technique'], $this->techniques) === false) {
            $data['new_technique'] = $data['technique'];
        }
        return array_map('trim', $data);
    }

    public function getArtworkInfoFromFilename(string $filename) : array
    {
        // default data
        $data = array(
            'width' => 1,
            'height' => 1
        );
        $data['title'] = (preg_match('/^(.+_)?T_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        $data['artist'] = (preg_match('/^(.+_)?A_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        $data['date'] = (preg_match('/^(.+_)?D_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        $data['technique'] = (preg_match('/^(.+_)?t_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        $data['dimensions'] = (preg_match('/^(.+_)?d_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        $data['place'] = (preg_match('/^(.+_)?l_([^_]+)_/', $filename, $matches)) ? $matches[2] : '';
        
        if (preg_match('/_Z_([0-9]+)x([0-9]+)_/', $filename, $matches)) {
            $data['width'] = $matches[1];
            $data['height'] = $matches[2];
        }
        return $data;
    }

    public function buildFilename(string $oldFilename, string $oldExtension, array $data) : string
    {
        if (empty($data['artist'])) {
            $data['artist'] = 'unknown';
        }
        if (!empty($data['new_artist'])) {
            $data['artist'] = $data['new_artist'];
        }
        if (empty($data['title'])) {
            $data['title'] = '-';
        }
        if (empty($data['date'])) {
            $data['date'] = '';
        }
        if (empty($data['technique'])) {
            $data['technique'] = '';
        }
        if (!empty($data['new_technique'])) {
            $data['technique'] = $data['new_technique'];
        }
        if (empty($data['dimensions'])) {
            $data['dimensions'] = '';
        }
        if (empty($data['place'])) {
            $data['place'] = '';
        }
        $data['uniqid'] = $this->getUniqId($oldFilename);
        $data['size'] = $this->getSize($oldFilename);
        return (
            'A_'.$data['artist'].'_'.
            'D_'.$data['date'].'_'.
            'T_'.$data['title'].'_'.
            't_'.$data['technique'].'_'.
            'd_'.$data['dimensions'].'_'.
            'l_'.$data['place'].'_'.
            'Z_'.$data['size'].'_.'.
            'U_'.$data['uniqid'].'_.'.
            $oldExtension
        );
    }


    public function getClassformArtistName(string $name, int $length = 0): string
    {
        if ($pos = strpos($name, ',')) {
            $name = \substr($name, 0, $pos);
        }
        $name = \iconv("UTF-8", "ISO-8859-1//TRANSLIT", $name);
        $name = \preg_replace('/([^a-z0-9])/i', '-', $name);
        return \strtolower($name);
    }

    public function getDirectory(string $filename)
    {
        return (preg_match('/^(.+_)?A_([^_]+)_/i', $filename, $matches)) ? $matches[2] : 'unknown';
    }

    public function getTechniques()
    {
        return array_combine($this->techniques, $this->techniques);
    }

    public function getUniqId(string $filename) : string
    {
        return (preg_match('/^.+_U_([^_]+)_$/i', $filename, $matches)) ? $matches[1] : uniqid();
    }
    public function getSize(string $filename) : string
    {
        return (preg_match('/^.+_Z_([^_]+)_/i', $filename, $matches)) ? $matches[1] : '';
    }
}
