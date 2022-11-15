<?php
/**
 * DESRIPTION:
 * DATE: 2022/11/14
 * TIME: 15:02
 * AUTHOR: lpower
 * PROJECT: example-app
 */
namespace App\Http\Controllers;

use App\Jobs\SaveInfoToDatabaseJob;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\DomCrawler\Crawler;

class OffsiteController extends Controller
{
    /**
     * Note: 获取电影相关信息
     * Create At: 2022/11/14 21:37
     * @return array
     * @throws GuzzleException
     * @author: lpower
     */
    public function getMovieInfo(): array
    {
        $baseUrl = "https://ssr1.scrape.center";
        $cards = [];
        for ($page = 1; $page <= 5; $page++) {
            $url = $baseUrl . '/page/' . $page;
            $client = new Client(['verify' => false]);
            $htmlData = $client->get($url);
            $html = $htmlData->getBody()->getContents();
            // index
            $crawler = new Crawler($html);
            $content = $crawler->filter("#index");
            // el-card
            $card = $content->filter(".el-card__body")->each(function (Crawler $node) use ($baseUrl) {
                $img = $node->filter("img")->attr('src');
                $allTitle = $node->filter(".m-b-sm")->text();
                $allTitle = str_replace(' ', '', $allTitle);
                $title = substr($allTitle, 0, strripos($allTitle, '-'));
                $enTitle = substr($allTitle, strrpos($allTitle, '-') + 1);
                $cateCount = $node->filter(".el-button")->count();
                $category = [];
                for ($i = 0; $i < $cateCount; $i++) {
                    $category[] = $node->filter(".el-button")->eq($i)->text();

                }
                $time = $node->filter(".m-v-sm")->text();
                $duration = preg_replace("/\D/s", '', $time);
                $releaseTime = $node->filter(".m-v-sm")->eq(1)->text();
                $score = $node->filter(".score")->text();
                $uri = $node->filter("a")->attr('href');
                $detailUrl = $baseUrl . $uri;
                $detailClient = new Client(['verify' => false]);
                $detailHtml = $detailClient->get($detailUrl)->getBody()->getContents();
                $detailCrawler = new Crawler($detailHtml);
                $detailContent = $detailCrawler->filter("#detail");
                $profile = $detailContent->filter(".el-card__body")->filter(".drama > p")->text();
                $actorCount = $detailContent->filter(".directors")->filter(".name")->count();
                $actor = [];
                for ($j = 0; $j < $actorCount; $j++) {
                    $actor[] = $detailContent->filter(".directors")->filter(".name")->eq($j)->text();
                }
                return [
                    'cover' => $img,
                    'title' => $title,
                    'en_title' => $enTitle,
                    'category' => $category,
                    'duration' => $duration,
                    'release_time' => str_replace(' 上映', '', $releaseTime),
                    'score' => $score,
                    'profile' => $profile,
                    'actor' => $actor
                ];
            });
            $cards = array_merge($cards, $card);
        }
        return $cards;
    }

    /**
     * Note: 保存信息到数据库
     * Create At: 2022/11/15 9:35
     * @return bool|string
     * @throws GuzzleException
     * @author: lpower
     */
    public function saveInfoToDatabase(): bool|string
    {
        try {
            $infos = $this->getMovieInfo();
            $rank = 1;
            DB::beginTransaction();
            foreach ($infos as $info) {
                $info['rank'] = $rank;
                $this->dispatch(new SaveInfoToDatabaseJob($info));
                $rank++;
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return $exception->getMessage();
        }
    }

    /**
     * Note: 显示页
     * Create At: 2022/11/15 10:44
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     * @author: lpower
     */
    public function index()
    {
        $ranks = DB::table('categories as a')
            ->select(['b.rank', 'b.title', 'a.movie_id'])
            ->leftJoin('movies as b', 'b.id', '=', 'a.movie_id')
            ->where([
                'b.is_delete' => 0,
                'a.category' => '奇幻'
            ])
            ->orderBy('b.rank')
            ->limit(5)
            ->get();

        foreach ($ranks as $rank) {
            $actors = $this->actors($rank->movie_id);
            $rank->actors = $actors;
        }


        $earliest = DB::table('movies')->where('is_delete', '=', 0)->where('release_time', '!=', '')->orderBy('release_time')->first();
        $earliest->actors = $this->actors($earliest->id);
        $latest = DB::table('movies')->where('is_delete', '=', 0)->orderByDesc('release_time')->first();
        $latest->actors = $this->actors($latest->id);
        return view('index', [
            'ranks' => $ranks,
            'earliest' => $earliest,
            'latest' => $latest
        ]);
    }

    /**
     * Note: 查询导演
     * Create At: 2022/11/15 10:41
     * @param $movieId
     * @return string
     * @author: lpower
     */
    public function actors($movieId)
    {
        $actors = DB::table('actors')->select('actor')->where(['id' => $movieId])->get()->toArray();
        $res = '';
        if (!is_null($actors)) {
            foreach ($actors as $actor) {
                $res .= $actor->actor . ' ';
            }
        }
        return $res;
    }
}
