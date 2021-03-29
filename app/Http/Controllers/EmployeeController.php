<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Employee;
use App\Models\Record;
use Validator;
use Storage;
use DB;
use PDF;

class EmployeeController extends Controller {

    public function index(Request $request) {

        try {

            $searchText = $request->get('search');

            if ($searchText) {

                $employees = Employee::With('records')
                        ->where('name', 'like', '%' . $searchText . '%')
                        ->Orwhere('last_name', 'like', '%' . $searchText . '%')
                        ->Orwhere('id', 'like', '%' . $searchText . '%')
                        ->where('is_active', 1)
                        ->get();
            } else {

                $employees = Employee::With('records')
                        ->where('is_active', 1)
                        ->get();
            }

            return response()->json($employees, 200);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function store(Request $request) {

        try {

            //subo el archivo csv de empleados si lo trae en el request
            if ($request->file) {
                $file = base64_decode($request->file);

                //Creo un nombre temporal único para el archivo con base a la fecha y hora de creación
                $filename = 'Temp' . "" . time() . '.csv';

                //Guardo el archivo en el storage
                Storage::disk('public')->put($filename, $file);

                $path = Storage::disk('public')->path($filename);

                $handle = fopen($path, "r");

                $header = 0;

                $array_csv = array();

                while (($data = fgetcsv($handle, 20000, ";") ) !== FALSE) {


                    if ($header != 0) {

                        $item = [
                            'name' => $data[0],
                            'last_name' => $data[1],
                            'card' => $data[2],
                            'identification_number' => $data[3],
                            'is_active' => 1,
                            'deparment' => $data[4],
                            'created_at' => date("Y-m-d H:i:s"),
                            'updated_at' => date("Y-m-d H:i:s"),
                        ];

                        array_push($array_csv, $item);
                    }

                    $header++;
                }

                DB::table("employees")->insert($array_csv);

                //Cierro el archivo
                fclose($handle);
                //Borro el archivo del storage
                Storage::disk('public')->delete($filename, $file);

                return response()->json('archivo cargado', 200);
            }

            $validator = Validator::make($request->all(), [
                        'name' => 'required',
                        'last_name' => 'required',
                        'card' => 'required',
                        'identification_number' => 'required',
                        'deparment' => 'required',
            ]);

            if ($validator->fails()) {
                Log::error('Falló la validación');
                return response()->json(['Falló la validación' => $validator->errors()], 422);
            }

            $employee = Employee::create([
                        'is_active' => 1,
                        'name' => $request->name,
                        'last_name' => $request->last_name,
                        'card' => $request->card,
                        'identification_number' => $request->identification_number,
                        'deparment' => $request->deparment,
            ]);


            if ($employee) {

                return response()->json(['success' => true, 'data' => $employee], 200);
            }


            return response()->json(['Falló la creación del registro'], 422);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function update(Request $request, $id) {

        try {

            $input = $request->all();

            $employee = Employee::find($id);

            if (!$employee) {

                return response()->json(['message' => 'No existe el Usuario'], 204);
            }

            $employee->update($input);

            Log::info('Empleado Actualizado : ' . $employee);

            return response()->json(['success' => true, 'data' => $employee, 'message' => 'Empleado actualizado con Éxito'], 200);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

    public function downloadPDF() {

        $records = Record::With('employee')
                ->get();

        $pdf = PDF::loadView('reportPDF', compact('records'));

        return $pdf->download('employees-report-recors.pdf');
    }

    public function entry(Request $request) {

        try {

            $validator = Validator::make($request->all(), [
                        'card' => 'required',
                        'identification_number' => 'required',
            ]);

            if ($validator->fails()) {
                Log::error('Falló la validación');
                return response()->json(['Falló la validación' => $validator->errors()], 422);
            }

            $employee = Employee::where('card', $request->card)
                    ->where('identification_number', $request->identification_number)
                    ->where('is_active', 1)
                    ->first();

            if (is_null($employee)) {


                $employee = Employee::where('card', $request->card)
                        ->first();

                if (is_null($employee)) {

                    Record::create([
                        'card' => $request->card,
                        'identification_number' => $request->identification_number,
                        'action' => 'fail',
                    ]);
                }

                Record::create([
                    'employee_id' => $employee->id,
                    'card' => $request->card,
                    'identification_number' => $request->identification_number,
                    'action' => 'fail',
                ]);

                return response()->json(['Falló la validación'], 422);
            }

            Record::create([
                'employee_id' => $employee->id,
                'card' => $request->card,
                'identification_number' => $request->identification_number,
                'action' => 'entry',
            ]);

            return response()->json($employee, 200);
        } catch (\Exception $e) {

            Log::critical('Error: ' . $e->getMessage() . ' Code: ' . $e->getCode());
            return response()->json($e->getMessage(), 500);
        }
    }

}
