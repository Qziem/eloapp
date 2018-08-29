open Types;

let kfactor = 32.0;

let compareCodes = (code1, code2) =>
  String.lowercase(code1) === String.lowercase(code2);

/* let modifyRating = (code, newRating, allUsers) =>
     List.map(
       user => user.code == code ? {...user, rating: newRating} : user,
       allUsers,
     );

   let getWinAndLooseUsers = (playersData, allUsers) => {
     let winCode = playersData.userWinCode;
     let looseCode = playersData.userLooseCode;

     let winUser =
       List.find(user => compareCodes(user.code, winCode), allUsers);
     let looseUser =
       List.find(user => compareCodes(user.code, looseCode), allUsers);

     (winUser, looseUser);
   };

   let calcRatings = (playersData, allUsers) => {
     let (winUser, looseUser) = getWinAndLooseUsers(playersData, allUsers);

     let transformetRatingWin = 10.0 ** (float_of_int(winUser.rating) /. 400.0);
     let transformetRatingLoose =
       10.0 ** (float_of_int(looseUser.rating) /. 400.0);

     let expectedScopeWin =
       transformetRatingWin /. (transformetRatingWin +. transformetRatingLoose);
     let expectedScopeLoose =
       transformetRatingLoose /. (transformetRatingWin +. transformetRatingLoose);

     let newRatingWin =
       winUser.rating + int_of_float(kfactor *. (1.0 -. expectedScopeWin));
     let newRatingLoose =
       looseUser.rating - int_of_float(kfactor *. expectedScopeLoose);

     allUsers
     |> modifyRating(winUser.code, newRatingWin)
     |> modifyRating(looseUser.code, newRatingLoose);
   }; */