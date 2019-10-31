# Projeto Api Crawler

# Instruções para instalação

* git clone https://github.com/juliodlima/restful_crawler.git

* composer install / update

* cp .env.example .env

* configurar APP_KEY=senhaCrawler no .env


# Instruções para uso

# Usar programa Postman para teste da busca:

    * metodo : "POST"
    * url : "api/busca"
    * passar a key no Headers "http-x-api-key" : "senhaCrawler"
    * passar a key no Headers "Content-Type" : "application/json"
    * passar os campos abaixo no Body
    * OBS: pode ser passado outro tipo de busca como, tipo_veiculo=moto, ou marca=honda, etc.

# Busca:
```Json
{
    "tipo_veiculo" : "carro",
    "marca" : "fiat",
    "modelo" : "palio",
    "ano_min" : "2016",
    "ano_max" : "2018",
    "preco_min" : "20000",
    "preco_max" : "50000",
    "km_min" : "10000",
    "km_max" : "50000",
    "zero_usado" : "usado",
    "revenda_particular" : "particular"       
}
```

# Usar programa Postman para teste dos detalhes:
    * metodo : "POST"
    * url : "api/detalhes"
    * passar a key no Headers "http-x-api-key" : "senhaCrawler"
    * passar a key no Headers "Content-Type" : "application/json"
    * passar os campos abaixo no Body
    * OBS: o link abaixo são os mesmos que foram retornado na Busca da Api anterior

# Detalhes:
```Json
{
    "link" : "https://seminovos.com.br/fiat-palio-2015-2016--2632596"
}
```