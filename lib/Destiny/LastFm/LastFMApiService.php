<?php 
namespace Destiny\LastFm;

use Destiny\Common\Service;
use Destiny\Common\Utils\Date;
use Destiny\Common\Config;
use Destiny\Common\Exception;
use Destiny\Common\Utils\Http;
use GuzzleHttp;

/**
 * @method static LastFMApiService instance()
 */
class LastFMApiService extends Service {
    
    /**
     * @return null|array
     */
    public function getLastPlayedTracks() {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('http://ws.audioscrobbler.com/2.0/', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => [
                'api_key' => Config::$a ['lastfm']['apikey'],
                'user' => Config::$a ['lastfm']['user'],
                'method' => 'user.getrecenttracks',
                'limit' => 3,
                'format' => 'json'
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            $json = GuzzleHttp\json_decode($response->getBody(), true);
            return $this->parseFeedResponse('recenttracks', $json);
        }
        return null;
    }

    /**
     * @return null|array
     */
    public function getTopTracks() {
        $client = new GuzzleHttp\Client(['timeout' => 10, 'connect_timeout' => 5, 'http_errors' => false]);
        $response = $client->get('http://ws.audioscrobbler.com/2.0/', [
            'headers' => ['User-Agent' => Config::userAgent()],
            'query' => [
                'api_key' => Config::$a ['lastfm']['apikey'],
                'user' => Config::$a ['lastfm']['user'],
                'method' => 'user.gettoptracks',
                'limit' => 3,
                'format' => 'json'
            ]
        ]);
        if($response->getStatusCode() == Http::STATUS_OK) {
            $json = GuzzleHttp\json_decode($response->getBody(), true);
            return $this->parseFeedResponse('toptracks', $json);
        }
        return null;
    }

    private function parseFeedResponse($rootNode, array $json) {
        if (! $json || isset ( $json ['error'] ) && $json ['error'] > 0 || count ( $json [$rootNode] ['track'] ) <= 0) {
            throw new Exception ( 'Error fetching tracks' );
        }
        foreach ( $json [$rootNode] ['track'] as $i => $track ) {
            // Timezone DST = -1
            if (! isset ( $track ['@attr'] ) || (!isset($track ['@attr'] ['nowplaying']) ||$track ['@attr'] ['nowplaying'] != true)) {
                if (! empty ( $track ['date'] )) {
                    $json [$rootNode] ['track'] [$i] ['date'] ['uts]'] = $track ['date'] ['uts'];
                    $json [$rootNode] ['track'] [$i] ['date_str'] = Date::getDateTime ( $track ['date'] ['uts'] )->format ( Date::FORMAT );
                }
            } else {
                $json [$rootNode] ['track'] [$i] ['date_str'] = '';
            }
        }
        return $json;
    }

}