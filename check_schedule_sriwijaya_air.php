<?php
/*
  Copyright (C) 2010 Sony Arianto Kurniawan <sony@sony-ak.com>

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see http://www.gnu.org/licenses/.

  ---------------------------------------------------------------------

  Script Name: check_schedule_sriwijaya_air.php
  Last Update: July 21, 2010
  Location of Last Update: Bangalore, India
*/

  $origin = $_GET['origin']; // IATA code
  $destination = $_GET['destination']; // IATA code
  $date = $_GET['date']; // dd/mm/yyyy format
  $passengerCount = $_GET['passenger_count'];
  $infantCount = $_GET['infant_count'];

  // sample query
  /*
  $origin = "KOE";
  $destination = "CGK";
  $date = "21/08/2010";
  $passengerCount = "1";
  $infantCount = "0";
  */

  // do access to sriwijaya air
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, "http://www.sriwijayaair-online.com/cek_schedule.php");
  curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($curl, CURLOPT_HEADER, 0);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_ENCODING, "");
  curl_setopt($curl, CURLOPT_POST, 1);
  curl_setopt($curl, CURLOPT_POSTFIELDS, "in_asal=" . $origin . "&in_arr=" . $destination . "&sd=" . $date . "&in_dewasa=" . $passengerCount . "&in_anak=" . $infantCount);
  $curlData = curl_exec($curl);
  curl_close($curl);

  $sriwijayaRaw = substr($curlData, strpos($curlData, "<div align=\"center\"><EM><b>FLIGHT SCHEDULE"));
  $sriwijayaRaw = substr($sriwijayaRaw, strpos($sriwijayaRaw, "<table width=\"70%\""));
  $sriwijayaRaw = substr($sriwijayaRaw, 0, strpos($sriwijayaRaw, "<table width=\"100%\""));
  $sriwijayaRaw = trim($sriwijayaRaw);
  $sriwijayaRaw = str_replace("<Br>", "<br/>", $sriwijayaRaw);
  $sriwijayaRaw = str_replace("<BR>", "<br/>", $sriwijayaRaw);
  $sriwijayaRaw = substr($sriwijayaRaw, 0, strlen($sriwijayaRaw) - 5);
  $sriwijayaRaw = trim($sriwijayaRaw);

  $arrSriwijayaTable = explode("</table>", $sriwijayaRaw);

  unset($arrSriwijayaTable[count($arrSriwijayaTable) - 1]);

  header("Content-Type: text/xml");
  echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
  echo "<sriwijayaair>";

  foreach ($arrSriwijayaTable as $sriwijayaTable) {
    $flightCode = substr($sriwijayaTable, strpos($sriwijayaTable, "<td width=\"31%\""));
    $flightCode = substr($flightCode, strpos($flightCode, "<b>") + 3);
    $flightCode = substr($flightCode, 0, strpos($flightCode, "</b>"));

    $departure = substr($sriwijayaTable, strpos($sriwijayaTable, "<td width=\"31%\""));
    $departure = substr($departure, strpos($departure, "<br/>"));
    $departure = substr($departure, strpos($departure, "<b>") + 3);
    $departure = substr($departure, 0, strpos($departure, "</b>"));

    $departure = trim($departure);
    $tmpArrival = $departure;

    $departure = substr($departure, 0, strpos($departure, "-"));
    $arrival = substr($tmpArrival, strpos($tmpArrival, "-") + 1);

    $priceRange = substr($sriwijayaTable, strpos($sriwijayaTable, "<td width=\"42%\""));
    $priceRange = substr($priceRange, strpos($priceRange, "<b>") + 3);
    $priceRange = substr($priceRange, 0, strpos($priceRange, "</b>"));
    $priceRange = str_replace("<br />", " ", $priceRange);

    $flightStatus = substr($sriwijayaTable, strpos($sriwijayaTable, "<td width=\"27%\""));
    $flightStatus = substr($flightStatus, strpos($flightStatus, "<b>") + 3);
    $flightStatus = substr($flightStatus, 0, strpos($flightStatus, "</b>"));

    echo "<schedule>";
    echo "<flightcode>" . $flightCode . "</flightcode>";
    echo "<departure>" . $departure . "</departure>";
    echo "<arrival>" . $arrival . "</arrival>";
    echo "<pricerange>" . $priceRange . "</pricerange>";
    echo "<flightstatus>" . $flightStatus . "</flightstatus>";
    echo "</schedule>";
  }

  echo "</sriwijayaair>";