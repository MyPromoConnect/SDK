<?php

namespace MyPromo\Connect\SDK\Repositories\Designs;

use Exception;
use GuzzleHttp\RequestOptions;
use MyPromo\Connect\SDK\Exceptions\ApiRequestException;
use MyPromo\Connect\SDK\Exceptions\ApiResponseException;
use MyPromo\Connect\SDK\Exceptions\SdkException;
use MyPromo\Connect\SDK\Exceptions\InputValidationException;
use MyPromo\Connect\SDK\Exceptions\InvalidResponseException;
use MyPromo\Connect\SDK\Models\Design;
use MyPromo\Connect\SDK\Repositories\Repository;
use Psr\Http\Message\ResponseInterface;

/**
 * Class DesignRepository
 * @package MyPromo\Connect\SDK\Repositories\Orders
 */
class DesignRepository extends Repository
{
    /**
     * @param Design $design
     * @return mixed
     * @throws ApiRequestException
     * @throws ApiResponseException
     * @throws InvalidResponseException
     */
    public function create(Design $design)
    {
        try {
            $response = $this->client->guzzle()->post('/v1/designs', [
                'headers'            => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->client->auth()->get(),
                ],
                RequestOptions::JSON => $design->toArray(),
            ]);
        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        if ($response->getStatusCode() !== 201) {
            throw new ApiResponseException($response->getBody(), $response->getStatusCode());
        }

        $body = json_decode($response->getBody(), true);

        if (!empty($body) && isset($body['id'])) {
            $design->setId($body['id']);
        } else {
            throw new InvalidResponseException('Unable retrive required data from response.', 422);
        }

        return $body;
    }

    /**
     * @param int $designId
     * @return mixed
     * @throws ApiRequestException
     * @throws ApiResponseException
     */
    public function find(int $designId)
    {
        try {
            $response = $this->client->guzzle()->get('/v1/designs/' . $designId, [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->client->auth()->get(),
                ],
            ]);

        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiResponseException($response->getBody(), $response->getStatusCode());
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * @param int $designId
     * @return mixed
     * @throws ApiRequestException
     * @throws ApiResponseException
     */
    public function submit(int $designId)
    {
        try {
            $response = $this->client->guzzle()->patch('/v1/designs/' . $designId . '/submit', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->client->auth()->get(),
                ],
            ]);
        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiResponseException($response->getBody(), $response->getStatusCode());
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * @param int $designId
     * @return mixed
     * @throws ApiRequestException
     * @throws ApiResponseException
     * @throws InputValidationException
     */
    public function getPreviewPDF(int $designId)
    {
        $getDesignResponse = $this->find($designId);

        $previewUrl = $getDesignResponse['preview_url'] ?? null;

        if ($previewUrl === null) {
            throw new InputValidationException("No preview url exists.", 422);
        }

        try {
            $response = $this->client->guzzle()->get($previewUrl, [
                'headers' => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ]);

        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        if ($response->getStatusCode() !== 200) {
            throw new ApiResponseException($response->getBody(), $response->getStatusCode());
        } elseif (
            strtolower($response->getHeader('Content-Type')[0]) !== 'pdf'
            && strtolower($response->getHeader('Content-Type')[0]) !== 'application/pdf'
        ) {
            throw new ApiResponseException(
                "Not supported content-type '{$response->getHeader('Content-Type')[0]}'."
            );
        }

        return $response;
    }

    /**
     * @param int $designId
     * @param string $targetFile
     * @return bool
     * @throws ApiRequestException
     * @throws SdkException
     */
    public function savePreview(int $designId, string $targetFile)
    {
        try {
            $response = $this->getPreviewPDF($designId);
        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        $previewFile = fopen($targetFile, 'w');

        if ($previewFile === false) {
            throw new SdkException("Preview '{$targetFile}' could not be created.");
        }

        if (fwrite($previewFile, $response->getbody()) === false) {
            throw new SdkException("Preview '{$targetFile}' is not writable.");
        }

        $file = fclose($previewFile);


        return $file;
    }

    /**
     * @param Design $design
     * @return mixed
     * @throws ApiRequestException
     * @throws ApiResponseException
     * @throws InvalidResponseException
     */
    public function createEditorUserHash(Design $design)
    {
        try {
            $response = $this->client->guzzle()->post('/v1/editor_user', [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . $this->client->auth()->get(),
                ],
            ]);

        } catch (Exception $ex) {
            throw new ApiRequestException($ex->getMessage(), $ex->getCode());
        }

        if ($response->getStatusCode() !== 201) {
            throw new ApiResponseException($response->getBody(), $response->getStatusCode());
        }

        $body = json_decode($response->getBody(), true);

        if (!empty($body) && isset($body['editor_user_hash'])) {
            $design->setEditorUserHash($body['editor_user_hash']);
        } else {
            throw new InvalidResponseException('Unable retrive required data from response.', 422);
        }

        return $body;
    }

}
