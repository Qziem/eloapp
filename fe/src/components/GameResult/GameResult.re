open EloTypes;
open Helpers;
open Svc;
open Js.Promise;
open BsReactstrap;

[%bs.raw {|require('./GameResult.scss')|}];

type saveStateType =
  | NOTHING
  | SAVING
  | WARNING(string)
  | SUCCESS(int);

type state = {
  userWinnerCode: string,
  userLooserCode: string,
  saveState: saveStateType,
};

type action =
  | ChangeWinUser(string)
  | ChangeLooseUser(string)
  | UpdateClick
  | SetSaveSuccess(int);

let component = ReasonReact.reducerComponent("GameResult");

let initialState = () => {
  userWinnerCode: "",
  userLooserCode: "",
  saveState: NOTHING,
};

let onSuccess = (containterSend, send, json) => {
  let ratingDiff = Json.Decode.field("ratingDiff", Json.Decode.int, json);
  send(SetSaveSuccess(ratingDiff));
  containterSend(GetUsersSvc);
};

let updateRatingsSvc = (state, users, containterSend) => {
  let winnerLooserNids = {
    winnerUserNid: getUserNidFromCode(state.userWinnerCode, users),
    looserUserNid: getUserNidFromCode(state.userLooserCode, users),
  };

  ReasonReact.UpdateWithSideEffects(
    {...state, saveState: SAVING},
    ({send}) => {
      let payload = EncodeUpdateRatings.encode(winnerLooserNids);
      svcPut("users/update_ratings", payload)
      |> then_(json => onSuccess(containterSend, send, json) |> resolve)
      |> ignore;
    },
  );
};

let updateStateWarningMsg = (state, msg: string) =>
  ReasonReact.Update({...state, saveState: WARNING(msg)});

let handleUpdateClickReducer = (state, users, containterSend) => {
  let winCode = state.userWinnerCode;
  let looseCode = state.userLooserCode;
  let winUserExist =
    List.exists(user => compareCodes(user.code, winCode), users);
  let looseUserExist =
    List.exists(user => compareCodes(user.code, looseCode), users);

  switch (winUserExist, looseUserExist) {
  | (true, true) =>
    !compareCodes(winCode, looseCode) ?
      updateRatingsSvc(state, users, containterSend) :
      updateStateWarningMsg(state, "Codes are the same")

  | (true, false) => updateStateWarningMsg(state, "Looser doesen't exist")
  | (false, true) => updateStateWarningMsg(state, "Winner doesen't exist")
  | (false, false) => updateStateWarningMsg(state, "Players doesen't exist")
  };
};

let reducer = (users, containterSend, action, state) =>
  switch (action) {
  | ChangeWinUser(value) =>
    ReasonReact.Update({...state, userWinnerCode: value})
  | ChangeLooseUser(value) =>
    ReasonReact.Update({...state, userLooserCode: value})
  | UpdateClick => handleUpdateClickReducer(state, users, containterSend)
  | SetSaveSuccess(ratingDiff) =>
    ReasonReact.Update({
      userWinnerCode: "",
      userLooserCode: "",
      saveState: SUCCESS(ratingDiff),
    })
  };

let valueFromEvent = event => ReactEvent.Form.target(event)##value;

let make = (~users, ~containterSend, ~disable, _children) => {
  ...component,
  initialState,
  reducer: reducer(users, containterSend),
  render: ({state, send}) =>
    <div className="gameResult">
      <div className="messageContainer">
        {
          switch (state.saveState) {
          | SUCCESS(ratingDiff) =>
            <div className="success">
              <span className="success-label">
                {"Updated, rating diff: " |> ReasonReact.string}
              </span>
              <span className="success-rating-diff">
                {string_of_int(ratingDiff) |> ReasonReact.string}
              </span>
            </div>
          | WARNING(msg) =>
            <div className="warning"> {msg |> ReasonReact.string} </div>
          | SAVING =>
            <div className="saving">
              {"Saving in progress..." |> ReasonReact.string}
            </div>
          | NOTHING => ReasonReact.null
          }
        }
      </div>
      <Form
        onSubmit={
          event => {
            event |> ReactEvent.Form.preventDefault;
            send(UpdateClick);
          }
        }>
        <Row>
          <Col>
            <FormGroup>
              <Label for_="winnerCode">
                {"Winner" |> ReasonReact.string}
              </Label>
              <Input type_="text" id="winnerCode" placeholder="code" />
            </FormGroup>
          </Col>
          <Col>
            <FormGroup>
              <Label for_="looserCode">
                {"Looser" |> ReasonReact.string}
              </Label>
              <Input type_="text" id="looserCode" placeholder="code" />
            </FormGroup>
          </Col>
          <Col className="colButton">
            <FormGroup>
              <Button
                color="primary"
                outline=true
                disabled={state.saveState === SAVING || disable}>
                {ReasonReact.string("Update")}
              </Button>
            </FormGroup>
          </Col>
        </Row>
      </Form>
    </div>,
};