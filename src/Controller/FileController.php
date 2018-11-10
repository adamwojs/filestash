<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\FileDownloadLimitException;
use App\Exception\FileNotFoundException;
use App\Service\FileOptions;
use App\Service\FileServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;

class FileController
{
    /** @var \App\Service\FileServiceInterface */
    private $fileService;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /**
     * @param \App\Service\FileServiceInterface $fileService
     * @param \Symfony\Component\Routing\RouterInterface $router
     */
    public function __construct(FileServiceInterface $fileService, RouterInterface $router)
    {
        $this->fileService = $fileService;
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $id
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/{id}/{filename}", name="download", methods={"GET"}, requirements={
     *     "filename"=".+"
     * })
     */
    public function download(Request $request, string $id, string $filename): Response
    {
        try {
            $file = $this->fileService->load($id);
            $data = $file->getDataStream();

            $response = new StreamedResponse();
            $response->setCallback(function () use ($data) {
                echo stream_get_contents($data);
            });
            $response->headers->set('Content-Type', $file->getMimeType());
            $response->prepare($request);

            return $response;
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (FileDownloadLimitException $e) {
            throw new GoneHttpException($e->getMessage(), $e);
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $filename
     * @param \App\Service\FileOptions $options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/{filename}", name="upload", methods={"PUT"}, requirements={
     *      "filename"=".+"
     * })
     */
    public function upload(Request $request, string $filename, FileOptions $options): Response
    {
        $id = $this->fileService->save($filename, $request->getContent(true), $options);

        $downloadUrl = $this->router->generate('download', [
            'id' => $id,
            'filename' => $filename,
        ], RouterInterface::ABSOLUTE_URL);

        return new Response(sprintf("Download URL: %s\n", $downloadUrl));
    }
}
