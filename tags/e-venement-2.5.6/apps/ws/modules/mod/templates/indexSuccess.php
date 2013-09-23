<h1>Remote authentications List</h1>

<table>
  <thead>
    <tr>
      <th>Id</th>
      <th>Sf guard user</th>
      <th>Ipaddress</th>
      <th>Active</th>
      <th>Created at</th>
      <th>Updated at</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($remote_authentications as $remote_authentication): ?>
    <tr>
      <td><a href="<?php echo url_for('mod/edit?id='.$remote_authentication->getId()) ?>"><?php echo $remote_authentication->getId() ?></a></td>
      <td><?php echo $remote_authentication->getSfGuardUserId() ?></td>
      <td><?php echo $remote_authentication->getIpaddress() ?></td>
      <td><?php echo $remote_authentication->getActive() ?></td>
      <td><?php echo $remote_authentication->getCreatedAt() ?></td>
      <td><?php echo $remote_authentication->getUpdatedAt() ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

  <a href="<?php echo url_for('mod/new') ?>">New</a>
