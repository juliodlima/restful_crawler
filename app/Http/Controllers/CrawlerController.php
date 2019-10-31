<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Goutte;

class CrawlerController extends Controller
{
    public $list = [];
    public $acessorios = [];
    public $aux = [];
    public $galeria = [];

    public function busca(Request $request)
    {   
        if ($request->header('http-x-api-key') == config('app.key')) {
            try {
                $end = "https://seminovos.com.br/";

                if (!is_null($request->input('tipo_veiculo'))) {
                    $end .= strtolower($request->input('tipo_veiculo')) . "/";
                }

                if (!is_null($request->input('marca'))) {
                    $end .= strtolower($request->input('marca')) . "/";
                }

                if (!is_null($request->input('modelo'))) {
                    $end .= strtolower($request->input('modelo')) . "/";
                }

                if (!is_null($request->input('ano_min')) && !is_null($request->input('ano_max'))) {
                    $end .= "ano-" . $request->input('ano_min') . "-" . $request->input('ano_max') . "/";
                }

                if (!is_null($request->input('preco_min')) && !is_null($request->input('preco_max'))) {
                    $end .= "preco-" . $request->input('preco_min') . "-" . $request->input('preco_max') . "/";
                }

                if (!is_null($request->input('km_min')) && !is_null($request->input('km_max'))) {
                    $end .= "km-" . $request->input('km_min') . "-" . $request->input('km_max') . "/";
                }

                if (!is_null($request->input('zero_usado'))) {
                    if (strtolower($request->input('zero_usado')) == "usado") {
                        $end .= "estado-usado/";
                    } elseif (strtolower($request->input('zero_usado')) == "zero") {
                        $end .= "estado-novo/";
                    }
                }

                if (!is_null($request->input('revenda_particular'))) {
                    if (strtolower($request->input('revenda_particular')) == "revenda") {
                        $end .= "origem-revenda/";
                    } elseif (strtolower($request->input('revenda_particular')) == "particular") {
                        $end .= "origem-particular/";
                    }
                }

                $end = substr($end, 0, -1);

                $this->list = [
                    "endereco" => $end
                ];

                $crawler = Goutte::request('GET', $end);

                $crawler->filter('body main > section.resultados-busca div.container div.veiculos-destaque div div.list-of-cards div.card-nitro-home')->each(function($node, $i) {
                    $this->acessorios = [];
                    $link = $node->filter('div.card-content a')->attr('href');
                    $link = explode("/", $link);

                    $this->list[$i]["link"] = "https://seminovos.com.br/" . $link[1];
                    $this->list[$i]["img"] = $node->filter('figure a img')->attr('src');
                    $this->list[$i]["marca_modelo"] = $node->filter('div.card-content a h2.card-title')->text();
                    $this->list[$i]["preco"] = $node->filter('div.card-content a span.card-price')->text();
                    $this->list[$i]["versao"] = substr($node->filter('div.card-content div.card-info div.card-features p.card-subtitle span')->text(),7,-1);
                    $this->list[$i]["ano"] = trim($node->filter("div.card-content div.card-info div.card-features ul.list-features li[title='Ano de fabricação']")->text());
                    $this->list[$i]["km"] = trim($node->filter("div.card-content div.card-info div.card-features ul.list-features li[title='Kilometragem atual']")->text());
                    $this->list[$i]["cambio"] = trim($node->filter("div.card-content div.card-info div.card-features ul.list-features li[title='Tipo de câmbio']")->text());

                    $listaAcessorios = $node->filter('div.card-content div.card-info div.card-features ul.list-inline li span')->each(function($itens) {
                        $this->acessorios[] = substr(trim($itens->text()),0,-1);
                    });
                    $this->list[$i]["acessorios"] = $this->acessorios;
                    $this->list[$i]["tipo_venda"] = trim($node->filter('div.card-content div.card-info div.card-features p.card-owner-label')->text());
                });

                $response = [
                    "response"  => $this->list,
                    "n"         => 200
                ];

            } catch (\Exception $e) {
                $response = [
                    "response"  => $e->message,
                    "n"         => 400
                ];
            }
        } else {
            $response = [
                "response"  => "Não autorizado!",
                "n"         => 401
            ];
        }

        return Response::make($response["response"], $response['n'])->header('Content-Type', 'application/json');
    }

    public function detalhes(Request $request)
    {
        if ($request->header('http-x-api-key') == config('app.key')) {
            try {
                if (!is_null($request->input('link'))) {
                    $end = $request->input('link');
                }

                $detalhe = [];
                $crawler = Goutte::request('GET', $end);
                $node = $crawler->filter('div.item-info');
                $detalhe["Link"] = $end;
                $detalhe["Marca/Modelo"] = $node->filter("h1[itemprop='name']")->text();
                $detalhe["Versão"] = trim($node->filter("div p.desc")->text());
                $detalhe["Preço"] = trim($node->filter("span")->text());
                $this->aux = [];

                $node->filter("div.attr-list dl dd")->each(function($itens) {
                    $this->aux[$itens->filter('span')->attr("title")] = $itens->filter('span')->text();
                });

                foreach ($this->aux as $key => $value) {
                    $detalhe[$key] = $value;
                }

                $detalhe["Foto"] = $crawler->filter("#fotoVeiculo img")->attr("src");

                $this->galeria = [];
                $crawler->filter('div.gallery-thumbs ul li')->each(function($mini) {
                    if ($mini->filter("img")->attr("class") == "available") {
                        $this->galeria[] = $mini->filter("img")->attr("data-src");
                    }
                });
                $detalhe["Galeria"] = $this->galeria;

                $this->acessorios = [];
                $crawler->filter('div.full-features ul li')->each(function($mini) {
                    $this->acessorios[] = $mini->filter("span")->text();
                });
                $detalhe["Acessórios"] = $this->acessorios;

                $detalhe["Observações"] = $crawler->filter('div.full-content p')->text();
                
                $response = [
                    "response"  => $detalhe,
                    "n"         => 200
                ];
                
            } catch (\Exception $e) {
                $response = [
                    "response"  => $e->message,
                    "n"         => 400
                ];
            }
        } else {
            $response = [
                "response"  => "Não autorizado!",
                "n"         => 401
            ];
        }

        return Response::make($response["response"], $response['n']) ->header('Content-Type', 'application/json');
    }
}
