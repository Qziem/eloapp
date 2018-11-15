[%bs.raw {|require('./ButtonWithConfirm.scss')|}];

type state =
  | INITIAL
  | CONFIRM;

type action =
  | SetConfirm
  | SetInitial;

let component = ReasonReact.reducerComponent("ButtonWithConfirm");

let initialState = () => INITIAL;

let reducer = (action, _state) =>
  switch (action) {
  | SetConfirm => ReasonReact.Update(CONFIRM)
  | SetInitial => ReasonReact.Update(INITIAL)
  };

let make = (~label, ~disabled, ~onYesClick, _children) => {
  ...component,
  initialState,
  reducer,
  render: ({state, send}) =>
    <div className="buttonWithConfirm">
      {
        switch (state) {
        | INITIAL =>
          <button disabled onClick=(_event => send(SetConfirm))>
            {label |> ReasonReact.string}
          </button>
        | CONFIRM =>
          <div className="confirmation">
            <div className="sureLabel">
              {"Are you sure?" |> ReasonReact.string}
            </div>
            <button
              className="yesButton"
              onClick=(
                _event => {
                  onYesClick();
                  send(SetInitial);
                }
              )>
              {"Yes" |> ReasonReact.string}
            </button>
            <button className="noButton" onClick=(_event => send(SetInitial))>
              {"No" |> ReasonReact.string}
            </button>
          </div>
        }
      }
    </div>,
};