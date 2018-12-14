<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Document;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		$response = [
			'success' => false
		];

		if ($documents = Document::orderBy('id','desc')->get())
		{
			$response = [
				'success' => true,
				'documents' => $documents
			];
		}

		return Response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
		$response = [
			'success' => false,
		];

		$doc = new Document;
		if ($doc->save())
		{
			$documents = Document::orderBy('id', 'desc')->get();
			$response = [
				'success' => true,
				'documents' => $documents,
				'selected' => $doc
			];
		}

		return Response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
		$response = [
			'success' => false,
			'document' => null
		];

		if (is_numeric($id) && $document = Document::where('id', $id)->first())
		{
			$response = [
				'success' => true,
				'document' => $document
			];
		}

		return Response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
		$response = [
			'success' => false,
		];
		$data = $request->all();

		if (is_numeric($id) && $document = Document::where('id', $id)->first())
		{
			preg_match('/(#+)(.*)/', trim($data['content']), $matches, PREG_OFFSET_CAPTURE);
			if (isset($matches) && count($matches) === 3)
			{
				$document->title = $matches[2][0];
			}

			$document->content = $data['content'];
			$document->save();

			$documents = Document::orderBy('id', 'desc')->get();
			$response = [
				'success' => true,
				'documents' => $documents
			];
		}
		return Response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		$response = [
			'success' => false,
			'documents' => [],
			'deleted' => null,
		];

		if (is_numeric($id) && $document = Document::where('id', $id)->first())
		{
			$document->delete();
			$documents = Document::orderBy('id', 'desc')->get();
			$response = [
				'success' => true,
				'documents' => $documents,
				'deleted' => $id
			];

		}
		return Response()->json($response, 200, [], JSON_NUMERIC_CHECK);
    }
}
