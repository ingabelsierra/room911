<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Reporte</title>
        <style>
        h1{
        text-align: center;
        text-transform: uppercase;
        }
        .contenido{
        font-size: 20px;
        }
       
    </style>
    </head>
    <body>
        <h3>Reporte de acceso al room911 por usuarios</h3>
        <hr>
        <div class="contenido">        
            
             <table>
                 <tr>
                    <th>Card</th>
                    <th>Identification</th>                     
                    <th>Name</th>
                    <th>Last Name</th>
                    <th>Action</th>
                    <th>Deparment</th>
                    <th>Date</th>
                </tr> 
                @foreach($records as $record)
                <tr>
                    <td>{{$record->card}}</td>
                    <td>{{$record->identification_number}}</td>  
                    
                        @if($record->employee)
                        <td>{{$record->employee->name}}</td>  
                        <td>{{$record->employee->last_name}}</td>
                        <td>{{$record->employee->deparment}}</td>
                        @else
                         <td>N/A</td>  
                        <td>N/A</td>
                        <td>N/A</td>
                       @endif                     
                    
                    <td>{{$record->action}}</td>                  
                    <td>{{$record->created_at}}</td>                    
                 
                </tr>   
                @endforeach
            </table>
        </div>
    </body>
</html>

