<?php

namespace App\Application\API;

use App\Application\CoinLoreCryptoDataSource\CoinLoreCryptoDataSource;
use App\Domain\Coin;
use App\Domain\Wallet;
use App\Infrastructure\Cache\WalletCache;
use Exception;
use Illuminate\Http\Response;


class SellCoinService
{
    private CoinLoreCryptoDataSource $coinLoreCryptoDataSource;
    private WalletCache $walletCache;

    public function __construct(CoinLoreCryptoDataSource $coinLoreCryptoDataSource)
    {
        $this->coinLoreCryptoDataSource = $coinLoreCryptoDataSource;
        $this->walletCache = new WalletCache();
    }

    /***
     * @throws Exception
     */
    public function execute(string $coin_id,string $wallet_id,float $amount_usd): string
    {
        try {
            $actual_coin = $this->coinLoreCryptoDataSource->getCoin($coin_id);
            $wallet = $this->exceptionWallet($coin_id,$wallet_id,$amount_usd);
            $this->sellCoin($coin_id, $actual_coin,$wallet_id,$wallet,$amount_usd);

        }catch (Exception $exception){
            throw new Exception($exception->getMessage(),$exception->getCode());
        }
        return "successful operation";
    }

    /***
     * @throws Exception
     */
    private function exceptionWallet(string $coin_id,string $wallet_id,float $amount_usd): Wallet{

        $wallet = $this->walletCache->get($wallet_id);

        if (!$wallet->existCoin($coin_id)){
            throw new Exception('A coin with specified ID was not found.',Response::HTTP_NOT_FOUND);
        }
        $amount_coin_wallet = $wallet->getAmountCoinByID($coin_id);

        if ($amount_coin_wallet < $amount_usd){
            throw new Exception('the quantity has been exceeded, you have '.$amount_coin_wallet.'.',Response::HTTP_NOT_FOUND);
        }
        return $wallet;
    }

    private function sellCoin(string $coin_id,Coin $actual_coin,string $wallet_id,Wallet $wallet,float $amount_usd): void{

        $actual_price_coin = $actual_coin->getPriceUsd();
        $wallet->setProfit($wallet->getProfit() + floatval($actual_price_coin)*$amount_usd);

        $coin = $wallet->getCoinByID($coin_id);
        $amount_coin_wallet = $wallet->getAmountCoinByID($coin_id);
        $wallet->setCoins($coin,$amount_coin_wallet - $amount_usd);

        $this->walletCache->set($wallet_id,$wallet);

    }


}