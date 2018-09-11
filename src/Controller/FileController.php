<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\FileNotFoundException;
use App\Service\FileServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;

class FileController
{
    private const HEADER_TTL = 'x-ttl';
    private const HEADER_MAX_DOWNLOADS = 'x-max-downloads';

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
            $stream = $this->fileService->load($id);

            $response = new StreamedResponse();
            $response->setCallback(function () use ($stream) {
                echo stream_get_contents($stream);
            });
            $response->headers->set('Content-Type', 'application/octet-stream');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $filename
            ));
            $response->prepare($request);

            return $response;
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException();
        }
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $filename
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @Route("/{filename}", name="upload", methods={"PUT"}, requirements={
     *      "filename"=".+"
     * })
     */
    public function upload(Request $request, string $filename): Response
    {
        $options = [];

        if ($request->headers->has(self::HEADER_TTL)) {
            $options['ttl'] = (int) $request->headers->get(self::HEADER_TTL);
        }

        if ($request->headers->has(self::HEADER_MAX_DOWNLOADS)) {
            $options['max_downloads'] = (int) $request->headers->get(self::HEADER_MAX_DOWNLOADS);
        }

        $id = $this->fileService->save($filename, $request->getContent(true));

        $downloadUrl = $this->router->generate('download', [
            'id' => $id,
            'filename' => $filename,
        ], RouterInterface::ABSOLUTE_URL);

        return new Response(sprintf("Download URL: %s\n", $downloadUrl));
    }
}
