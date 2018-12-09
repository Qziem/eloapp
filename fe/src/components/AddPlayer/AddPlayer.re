open Svc;
open Js.Promise;
[%bs.raw {|require('./AddPlayer.scss')|}];

type savingState =
  | NOTHING
  | SAVING
  | WARNING(string)
  | SUCCESS;

type state = {
  code: string,
  name: string,
  savingState,
};

type action =
  | ChangeCode(string)
  | ChangeName(string)
  | SetSavingState(savingState)
  | AddPlayer;

let component = ReasonReact.reducerComponent("AddPlayer");

let initialState = () => {code: "", name: "", savingState: NOTHING};

let onSuccess = send => send(SetSavingState(SUCCESS));

let addPlayerSvc = state => {
  let payload =
    Json.Encode.object_([
      ("code", Json.Encode.string(state.code)),
      ("name", Json.Encode.string(state.name)),
    ]);
  ReasonReact.UpdateWithSideEffects(
    {...state, savingState: SAVING},
    ({send}) =>
      svcPost("users", payload)
      |> then_(_json => onSuccess(send) |> resolve)
      |> ignore,
  );
};

let addPlayerReducer = state => {
  let codeValid = String.trim(state.code) !== "";
  let nameValid = String.trim(state.name) !== "";
  switch (codeValid, nameValid) {
  | (true, true) => addPlayerSvc(state)
  | _ =>
    ReasonReact.Update({
      ...state,
      savingState: WARNING("Fields can not be empty"),
    })
  };
};

let reducer = (action, state) =>
  switch (action) {
  | ChangeCode(code) => ReasonReact.Update({...state, code})
  | ChangeName(name) => ReasonReact.Update({...state, name})
  | SetSavingState(SUCCESS) =>
    ReasonReact.Update({code: "", name: "", savingState: SUCCESS})
  | SetSavingState(savingState) =>
    ReasonReact.Update({...state, savingState})
  | AddPlayer => addPlayerReducer(state)
  };

let make = _children => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="addPlayer">
      {
        switch (state.savingState) {
        | NOTHING => ReasonReact.null
        | SUCCESS =>
          <div className="success">
            {"Successfully saved :)" |> ReasonReact.string}
          </div>
        | SAVING =>
          <div className="saving">
            {"Removing in progress..." |> ReasonReact.string}
          </div>
        | WARNING(msg) =>
          <div className="warning"> {ReasonReact.string(msg)} </div>
        }
      }
      <form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(AddPlayer);
          }
        }>
        <table>
          <tbody>
            <tr>
              <td>
                <input
                  placeholder="code"
                  value={state.code}
                  onChange={
                    event =>
                      send(ChangeCode(GameResult.valueFromEvent(event)))
                  }
                />
              </td>
              <td>
                <input
                  placeholder="name"
                  value={state.name}
                  onChange={
                    event =>
                      send(ChangeName(GameResult.valueFromEvent(event)))
                  }
                />
              </td>
              <td> <button> {ReasonReact.string("Add")} </button> </td>
            </tr>
          </tbody>
        </table>
      </form>
    </div>,
};