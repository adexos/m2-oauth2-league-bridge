<?php

declare(strict_types=1);

namespace Adexos\Oauth2LeagueBridge\Decoder;

use Adexos\Oauth2LeagueBridge\Decoder\Model\JwtResultInterface;
use Adexos\Oauth2LeagueBridge\Exception\WrongJwtClassDecoderException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use function class_exists;
use function explode;
use function is_subclass_of;
use function json_encode;

class JwtDecoder
{
    private const ALGORITHM = 'RS256';

    private SerializerInterface $serializer;

    private LoggerInterface $logger;

    public function __construct(SerializerInterface $serializer, LoggerInterface $logger)
    {
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * @throws JsonException
     * @throws WrongJwtClassDecoderException
     */
    public function decode(string $key, string $targetClass, string $publicKey, bool $decodeWithKey = true): JwtResultInterface
    {
        if (!class_exists($targetClass) || !is_subclass_of($targetClass, JwtResultInterface::class)) {
            throw new WrongJwtClassDecoderException();
        }

        if ($decodeWithKey) {
            try {

                $jwtDecoded = json_encode(
                    JWT::decode(
                        $key,
                        new Key($publicKey, self::ALGORITHM)
                    ),
                    JSON_THROW_ON_ERROR
                );
            } catch (Exception $e) {
                $this->logger->alert($e, ['context' => 'jwt_decode']);
                throw $e;
            }
        } else {
            $this->logger->error('Jwt public sign key is disabled, this should be a temporary set only used for testing. Please reintroduce the sign check ASAP.');

            $oauthToken = explode('.', $key);
            [, $bodyb64,] = $oauthToken;

            $jwtDecoded = JWT::urlsafeB64Decode($bodyb64);
        }

        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->serializer->deserialize($jwtDecoded, $targetClass, JsonEncoder::FORMAT);
    }
}
