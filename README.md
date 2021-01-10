# The社史クリエイター via 有価証券報告書

## 仕様
- 上場企業の情報取得の自動化ツール
- 有価証券報告書（XBRL）を読み込むことで
- 企業情報をAPIとして返却する

<img src="https://the-shashi.com/img-common/shashi-creator.png">

## API仕様書
 - API[GET]
 - http://localhost/api/company/create/{stock_code}
   - stock_code: 上場企業コード(数字4桁)
 - key => value は気が向いたら作ります！ by Open API 3.0

## 取得例[GET]
 - http://localhost/api/company/create/2670

## 技術
 - Laravel + Docker (nginx + php + MySQL)
 - Localで完結